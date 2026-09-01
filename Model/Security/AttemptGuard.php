<?php
/**
 * Copyright © 2025 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Model\Security;

use Line\Payment\Api\Data\ConfigInterface;
use Line\Payment\Model\ResourceModel\PaymentAttempt;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Exception\SecurityViolationException;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Decides whether an authorization attempt may reach the gateway.
 */
class AttemptGuard
{
    /**
     * Thresholds that apply while the store circuit breaker is tripped.
     */
    private const STRICT_THRESHOLDS = [
        AttemptContext::DIMENSION_CARD => 1,
        AttemptContext::DIMENSION_BIN => 5
    ];

    /**
     * Dimensions that only inform, never block.
     */
    private const ADVISORY_DIMENSIONS = [
        AttemptContext::DIMENSION_STORE,
        AttemptContext::DIMENSION_BIN,
        AttemptContext::DIMENSION_EMAIL
    ];

    private ConfigInterface $config;
    private PaymentAttempt $resource;
    private AttemptWindow $window;
    private CacheInterface $cache;
    private LoggerInterface $logger;

    /**
     * @param ConfigInterface $config
     * @param PaymentAttempt $resource
     * @param AttemptWindow $window
     * @param CacheInterface $cache
     * @param LoggerInterface $logger
     */
    public function __construct(
        ConfigInterface $config,
        PaymentAttempt $resource,
        AttemptWindow $window,
        CacheInterface $cache,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->resource = $resource;
        $this->window = $window;
        $this->cache = $cache;
        $this->logger = $logger;
    }

    /**
     * @param AttemptContext $context
     *
     * @return void
     * @throws SecurityViolationException
     */
    public function assertAllowed(AttemptContext $context): void
    {
        if (!$this->config->isAntifraudEnabled()) {
            return;
        }

        $keys = $context->getKeys();
        $cutoff = $this->window->cutoff();
        $counts = $this->resource->countsFor($keys, $context->getStoreId(), $cutoff);

        $strict = $this->isBreakerTripped($counts, $context, $cutoff);

        foreach ($keys as $dimension => $value) {
            $threshold = $this->getThreshold($dimension, $strict);
            $declines = $counts[$dimension] ?? 0;

            if ($threshold <= 0 || $declines < $threshold) {
                continue;
            }

            $isAdvisory = in_array($dimension, self::ADVISORY_DIMENSIONS, true);

            if ($isAdvisory && !$this->shouldReportAdvisory($context, $dimension, $value)) {
                continue;
            }

            $this->logger->log(
                $isAdvisory ? LogLevel::WARNING : LogLevel::ERROR,
                $isAdvisory
                    ? 'Line Payment: advisory card testing threshold crossed (not blocking).'
                    : 'Line Payment: authorization attempt blocked by the card testing throttle.',
                [
                    'dimension' => $dimension,
                    'value' => $this->maskValue($dimension, $value),
                    'declines' => $declines,
                    'threshold' => $threshold,
                    'window_minutes' => $this->window->getMinutes(),
                    'quote_id' => $context->getQuoteId(),
                    'store_id' => $context->getStoreId(),
                    'strict_mode' => $strict
                ]
            );

            if ($isAdvisory) {
                continue;
            }

            throw new SecurityViolationException(
                __('We are unable to process this payment right now. Please try a different payment method or contact us.')
            );
        }
    }

    /**
     * @param array<string, int> $counts
     * @param AttemptContext $context
     * @param string $cutoff
     *
     * @return bool
     */
    private function isBreakerTripped(array $counts, AttemptContext $context, string $cutoff): bool
    {
        $breaker = $this->config->getAntifraudStoreBreaker();
        $storeDeclines = $counts[AttemptContext::DIMENSION_STORE] ?? 0;

        if ($breaker <= 0 || $storeDeclines < $breaker) {
            return false;
        }

        $this->reportBreaker($context, $storeDeclines, $breaker, $cutoff);

        return true;
    }

    /**
     * One line per window, not one per blocked request.
     *
     * @param AttemptContext $context
     * @param int $storeDeclines
     * @param int $breaker
     * @param string $cutoff
     *
     * @return void
     */
    private function reportBreaker(AttemptContext $context, int $storeDeclines, int $breaker, string $cutoff): void
    {
        $flag = 'line_payment_breaker_' . $context->getStoreId();

        if ($this->cache->load($flag)) {
            return;
        }

        $this->cache->save('1', $flag, [], $this->window->getMinutes() * 60);

        $this->logger->critical('Line Payment: store wide decline rate tripped the circuit breaker.', [
            'store_id' => $context->getStoreId(),
            'declines' => $storeDeclines,
            'breaker' => $breaker,
            'window_minutes' => $this->window->getMinutes(),
            'top_bins' => $this->resource->topBins($context->getStoreId(), $cutoff)
        ]);
    }

    /**
     * One advisory line per key per window, same flag pattern the circuit breaker uses.
     *
     * The key includes the dimension value, so a second BIN or a second email crossing its
     * threshold still gets its own entry - only repetition of the same key is suppressed.
     *
     * @param AttemptContext $context
     * @param string $dimension
     * @param string $value
     *
     * @return bool
     */
    private function shouldReportAdvisory(AttemptContext $context, string $dimension, string $value): bool
    {
        $flag = 'line_payment_advisory_' . sha1(implode('|', [
            (string) $context->getStoreId(),
            $dimension,
            $value
        ]));

        if ($this->cache->load($flag)) {
            return false;
        }

        $this->cache->save('1', $flag, [], $this->window->getMinutes() * 60);

        return true;
    }

    /**
     * @param string $dimension
     * @param bool $strict
     *
     * @return int
     */
    private function getThreshold(string $dimension, bool $strict): int
    {
        $threshold = $this->config->getAntifraudThreshold($dimension);

        if ($strict && isset(self::STRICT_THRESHOLDS[$dimension])) {
            return min($threshold, self::STRICT_THRESHOLDS[$dimension]);
        }

        return $threshold;
    }

    /**
     * The BIN is an issuer prefix and is logged in full so an operator can act on it. The card
     * fingerprint and the email hash are truncated: enough to correlate log lines, not enough to
     * confirm a guess offline.
     *
     * @param string $dimension
     * @param string $value
     *
     * @return string
     */
    private function maskValue(string $dimension, string $value): string
    {
        if ($dimension === AttemptContext::DIMENSION_CARD || $dimension === AttemptContext::DIMENSION_EMAIL) {
            return substr($value, 0, 8) . '...';
        }

        return $value;
    }
}
