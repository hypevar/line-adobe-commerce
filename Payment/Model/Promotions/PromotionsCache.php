<?php
/**
 * Copyright © 2025 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Model\Promotions;

use Line\Payment\Api\Data\ConfigInterface;
use Line\Payment\Model\StoreConfigResolver;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Short-lived cache for the outbound promotions and emitters payloads.
 *
 * The customer's own BIN lookup at checkout warms the entry that the request builder reads a few
 * seconds later at authorization time, so in practice resolving the installment plan costs no
 * extra outbound call.
 */
class PromotionsCache
{
    public const CACHE_TAG = 'LINE_PAYMENT_PROMOTIONS';

    private CacheInterface $cache;
    private ConfigInterface $config;
    private StoreConfigResolver $storeResolver;
    private Json $serializer;

    /**
     * @param CacheInterface $cache
     * @param ConfigInterface $config
     * @param StoreConfigResolver $storeResolver
     * @param Json $serializer
     */
    public function __construct(
        CacheInterface $cache,
        ConfigInterface $config,
        StoreConfigResolver $storeResolver,
        Json $serializer
    ) {
        $this->cache = $cache;
        $this->config = $config;
        $this->storeResolver = $storeResolver;
        $this->serializer = $serializer;
    }

    /**
     * @param string $bucket
     * @param string $discriminator
     *
     * @return array|null
     */
    public function load(string $bucket, string $discriminator = ''): ?array
    {
        if ($this->getLifetime() <= 0) {
            return null;
        }

        $payload = $this->cache->load($this->key($bucket, $discriminator));

        if (!is_string($payload) || $payload === '') {
            return null;
        }

        try {
            $data = $this->serializer->unserialize($payload);
        } catch (\InvalidArgumentException $exception) {
            return null;
        }

        return is_array($data) ? $data : null;
    }

    /**
     * An error envelope or an empty payload is never stored: a momentary failure upstream must not
     * be served back as an answer for the whole lifetime.
     *
     * @param string $bucket
     * @param string $discriminator
     * @param array $payload
     *
     * @return void
     */
    public function save(string $bucket, string $discriminator, array $payload): void
    {
        $lifetime = $this->getLifetime();

        if ($lifetime <= 0 || $payload === [] || !empty($payload['errors']) || !empty($payload['error'])) {
            return;
        }

        $this->cache->save(
            $this->serializer->serialize($payload),
            $this->key($bucket, $discriminator),
            [self::CACHE_TAG],
            $lifetime
        );
    }

    /**
     * The account pair is part of the key: two stores pointing at different marketplaces must not
     * read each other's promotions.
     *
     * @param string $bucket
     * @param string $discriminator
     *
     * @return string
     */
    private function key(string $bucket, string $discriminator): string
    {
        [$marketplace, $account] = $this->config->getPromotionsCredentials();

        return 'line_payment_' . sha1(implode('|', [
            $bucket,
            (string) $this->storeResolver->getStoreId(),
            (string) $marketplace,
            (string) $account,
            $discriminator
        ]));
    }

    /**
     * @return int
     */
    private function getLifetime(): int
    {
        return $this->config->getPromotionsCacheLifetime();
    }
}
