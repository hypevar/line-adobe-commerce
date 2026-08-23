<?php
/**
 * Copyright © 2024 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Model;

use Line\Payment\Api\GetPromotionsActionInterface;
use Line\Payment\Model\Promotions\Adapter;
use Line\Payment\Model\Promotions\DataExtractor\PromotionsWithoutBank;
use Line\Payment\Model\Promotions\Exception\PromotionsUnavailable;
use Line\Payment\Model\Promotions\PromotionsCache;
use Psr\Log\LoggerInterface;

/**
 * Action wrapper for Promotions retrieval
 */
class GetPromotionsAction implements GetPromotionsActionInterface
{
    private const CACHE_BUCKET = 'promotions_all';

    private Adapter $client;
    private PromotionsWithoutBank $promotionsWithoutBank;
    private PromotionsCache $cache;
    private LoggerInterface $log;

    /**
     * @param Adapter $client
     * @param PromotionsWithoutBank $promotionsWithoutBank
     * @param PromotionsCache $cache
     * @param LoggerInterface $logger
     */
    public function __construct(
        Adapter $client,
        PromotionsWithoutBank $promotionsWithoutBank,
        PromotionsCache $cache,
        LoggerInterface $logger
    ) {
        $this->client = $client;
        $this->promotionsWithoutBank = $promotionsWithoutBank;
        $this->cache = $cache;
        $this->log = $logger;
    }

    /**
     * @param string $cardBrand
     *
     * @return PromotionsInterface[]
     */
    public function get(string $cardBrand = ''): array
    {
        $cached = $this->cache->load(self::CACHE_BUCKET);

        if ($cached === null) {
            try {
                $cached = $this->client->get(self::ENDPOINT_URL, [], false);
            } catch (\Throwable $e) {
                $this->log->error('Promotions lookup failed: ' . $e->getMessage());

                throw new PromotionsUnavailable(
                    __('Card promotions are not available right now. Please try again in a few minutes.'),
                    $e
                );
            }

            $this->cache->save(self::CACHE_BUCKET, '', $cached);
        }

        if ($cardBrand) {
            return $this->promotionsWithoutBank->extractByCardBrand($cardBrand, $cached);
        }

        return $cached;
    }
}
