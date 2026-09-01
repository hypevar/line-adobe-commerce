<?php
/**
 * Copyright © 2024 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Model;

use Line\Payment\Api\GetPromotionsByBinActionInterface;
use Line\Payment\Model\Promotions\Adapter;
use Line\Payment\Model\Promotions\Exception\PromotionsUnavailable;
use Line\Payment\Model\Promotions\PromotionsCache;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

/**
 * Action wrapper for Promotions retrieval
 */
class GetPromotionsByBinAction implements GetPromotionsByBinActionInterface
{
    private const CACHE_BUCKET = 'promotions_by_bin';
    private const STATUS_NOT_FOUND = 404;

    private const NO_PROMOTIONS = [
        'promotions' => [],
        'cardBrand' => '',
        'defaultMerchant' => false
    ];

    protected Adapter $client;
    protected LoggerInterface $log;
    private PromotionsCache $cache;

    /**
     * @param Adapter $client
     * @param PromotionsCache $cache
     * @param LoggerInterface $logger
     */
    public function __construct(
        Adapter $client,
        PromotionsCache $cache,
        LoggerInterface $logger
    ) {
        $this->client = $client;
        $this->cache = $cache;
        $this->log = $logger;
    }

    /**
     * A 404 is the service saying this BIN has no promotions, which is a legitimate answer and
     * yields an empty result. Every other failure is an outage and stops the order.
     *
     * @param string $value BIN value to be sent to the Gateway
     *
     * @return array
     * @throws LocalizedException when the BIN is not a BIN
     * @throws PromotionsUnavailable when the service cannot be reached
     */
    public function get(string $value): array
    {
        $bin = $this->normalise($value);

        $cached = $this->cache->load(self::CACHE_BUCKET, $bin);

        if ($cached !== null) {
            return $cached;
        }

        try {
            $promotions = $this->client->get(
                self::ENDPOINT_URL,
                [],
                true,
                [self::PARAM_BIN_NAME => $bin]
            );
        } catch (PromotionsUnavailable $e) {
            if ($e->getCode() !== self::STATUS_NOT_FOUND) {
                throw $this->unavailable($e, $bin);
            }

            $promotions = self::NO_PROMOTIONS;
        } catch (\Throwable $e) {
            throw $this->unavailable($e, $bin);
        }

        $this->cache->save(self::CACHE_BUCKET, $bin, $promotions);

        return $promotions;
    }

    /**
     * Wraps a lookup failure in the only message the customer is allowed to see.
     *
     * @param \Throwable $cause
     * @param string $bin
     *
     * @return PromotionsUnavailable
     */
    private function unavailable(\Throwable $cause, string $bin): PromotionsUnavailable
    {
        $this->log->error('Promotions lookup by BIN failed: ' . $cause->getMessage(), ['bin' => $bin]);

        return new PromotionsUnavailable(
            __('Card promotions are not available right now. Please try again in a few minutes.'),
            $cause instanceof \Exception ? $cause : null
        );
    }

    /**
     * @param string $value
     *
     * @return string
     * @throws LocalizedException
     */
    private function normalise(string $value): string
    {
        $bin = (string) preg_replace('/\D/', '', $value);

        if (!preg_match('/^\d{6,8}$/', $bin)) {
            throw new LocalizedException(__('The card number is not valid.'));
        }

        return $bin;
    }
}
