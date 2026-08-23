<?php
/**
 * Copyright © 2025 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Cron;

use Line\Payment\Model\ResourceModel\PaymentAttempt;
use Line\Payment\Model\Security\AttemptWindow;
use Psr\Log\LoggerInterface;

/**
 * Drops decline counters that can no longer influence a decision.
 */
class PruneAttempts
{
    private PaymentAttempt $resource;
    private AttemptWindow $window;
    private LoggerInterface $logger;

    /**
     * @param PaymentAttempt $resource
     * @param AttemptWindow $window
     * @param LoggerInterface $logger
     */
    public function __construct(
        PaymentAttempt $resource,
        AttemptWindow $window,
        LoggerInterface $logger
    ) {
        $this->resource = $resource;
        $this->window = $window;
        $this->logger = $logger;
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        try {
            $removed = $this->resource->prune($this->window->pruneCutoff());
        } catch (\Throwable $exception) {
            $this->logger->error('Line Payment: pruning the attempt counters failed: ' . $exception->getMessage());

            return;
        }

        if ($removed > 0) {
            $this->logger->debug(sprintf('Line Payment: pruned %d expired attempt counters.', $removed));
        }
    }
}
