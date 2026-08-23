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
        } catch (\Throwable $e) {
            $this->log->error('Promotions lookup by BIN failed: ' . $e->getMessage(), ['bin' => $bin]);

            throw new PromotionsUnavailable(
                __('Card promotions are not available right now. Please try again in a few minutes.'),
                $e
            );
        }

        $this->cache->save(self::CACHE_BUCKET, $bin, $promotions);

        return $promotions;
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
