<?php
/**
 * Copyright © 2025 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Model\Security;

use Line\Payment\Api\Data\ConfigInterface;
use Line\Payment\Model\ResourceModel\PaymentAttempt;
use Psr\Log\LoggerInterface;

/**
 * Adds one decline to every counter the attempt belongs to.
 *
 * Only declines are counted. Counting attempts would throttle a healthy store's ordinary traffic
 * and would make the BIN dimension useless, and a customer whose card succeeds never approaches a
 * threshold.
 */
class AttemptRecorder
{
    private ConfigInterface $config;
    private PaymentAttempt $resource;
    private AttemptWindow $window;
    private LoggerInterface $logger;

    /**
     * @param ConfigInterface $config
     * @param PaymentAttempt $resource
     * @param AttemptWindow $window
     * @param LoggerInterface $logger
     */
    public function __construct(
        ConfigInterface $config,
        PaymentAttempt $resource,
        AttemptWindow $window,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->resource = $resource;
        $this->window = $window;
        $this->logger = $logger;
    }

    /**
     * @param AttemptContext $context
     *
     * @return void
     */
    public function record(AttemptContext $context): void
    {
        if (!$this->config->isAntifraudEnabled()) {
            return;
        }

        $now = $this->window->now();
        $cutoff = $this->window->cutoff();

        foreach ($context->getKeys() as $dimension => $value) {
            try {
                $this->resource->increment($dimension, $value, $context->getStoreId(), $now, $cutoff);
            } catch (\Throwable $exception) {
                $this->logger->error(
                    'Line Payment: could not record a decline for the card testing throttle.',
                    ['dimension' => $dimension, 'error' => $exception->getMessage()]
                );
            }
        }
    }
}
