<?php
/**
 * Copyright © 2024 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Model;

use Line\Payment\Api\GetPromotionsByBinActionInterface;
use Line\Payment\Model\Promotions\Adapter;
use Psr\Log\LoggerInterface;

/**
 * Action wrapper for Promotions retrieval
 */
class GetPromotionsByBinAction implements GetPromotionsByBinActionInterface
{
    protected Adapter $client;
    protected LoggerInterface $log;

    public function __construct(
        Adapter $client,
        LoggerInterface $logger
    ) {
        $this->client = $client;
        $this->log = $logger;
    }

    public function get(string $value): array
    {
        $promotions = [];

        $url = self::ENDPOINT_URL . '?bin=' . $value;

        try {
            $promotions = $this->client->get($url, [], true);

        } catch (\Exception $e) {
            $this->log->error($e->getMessage());

            $promotions = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        return $promotions;
    }
}
