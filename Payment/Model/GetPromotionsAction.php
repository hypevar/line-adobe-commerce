<?php
/**
 * Copyright © 2024 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Model;

use Line\Payment\Api\GetPromotionsActionInterface;
use Line\Payment\Model\Promotions\Adapter;
use Line\Payment\Model\Promotions\DataExtractor\PromotionsWithoutBank;
use Psr\Log\LoggerInterface;

/**
 * Action wrapper for Promotions retrieval
 */
class GetPromotionsAction implements GetPromotionsActionInterface
{
    private Adapter $client;
    private PromotionsWithoutBank $promotionsWithoutBank;
    private LoggerInterface $log;

    public function __construct(
        Adapter $client,
        PromotionsWithoutBank $promotionsWithoutBank,
        LoggerInterface $logger
    ) {
        $this->client = $client;
        $this->promotionsWithoutBank = $promotionsWithoutBank;
        $this->log = $logger;
    }

    /**
     * @param string $cardBrand
     *
     * @return PromotionsInterface[]
     */
    public function get(string $cardBrand = ''): array
    {
        $promotions = [];

        try {
            $promotions = $this->client->get(self::ENDPOINT_URL, [], false);

            // if brand has been passed, we'll pull all promotions without a bank
            // this case is for when no promotions where retrieved by BIN,
            // and we need to pull promotions applied to All CardBrands (from all Banks)
            if ($cardBrand) {
                $promotions = $this->promotionsWithoutBank->extractByCardBrand($cardBrand, $promotions);
            }

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
