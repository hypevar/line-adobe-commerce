<?php
/**
 * Copyright © 2024 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Line\Payment\Api\Data\Config\SSLVersionsInterface;
use Line\Payment\Api\Data\ConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class for accessing module's configuration option values
 */
class Config implements ConfigInterface
{
    /**
     * Maps a throttle dimension onto the configuration path holding its threshold
     */
    private const ANTIFRAUD_THRESHOLD_PATHS = [
        'card' => self::XPATH_ANTIFRAUD_MAX_DECLINES_CARD,
        'bin' => self::XPATH_ANTIFRAUD_MAX_DECLINES_BIN,
        'quote' => self::XPATH_ANTIFRAUD_MAX_DECLINES_QUOTE,
        'email' => self::XPATH_ANTIFRAUD_MAX_DECLINES_EMAIL,
        'customer' => self::XPATH_ANTIFRAUD_MAX_DECLINES_CUSTOMER,
        'ip' => self::XPATH_ANTIFRAUD_MAX_DECLINES_IP
    ];

    private StoreConfigResolver $storeConfigResolver;
    private ScopeConfigInterface $config;
    private Json $serializer;
    private EncryptorInterface $encryptor;

    /**
     * @param StoreConfigResolver $storeConfigResolver
     * @param ScopeConfigInterface $scopeConfig
     * @param Json $serializer
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        StoreConfigResolver $storeConfigResolver,
        ScopeConfigInterface $scopeConfig,
        Json $serializer,
        EncryptorInterface $encryptor
    ) {
        $this->storeConfigResolver = $storeConfigResolver;
        $this->config = $scopeConfig;
        $this->serializer = $serializer;
        $this->encryptor = $encryptor;
    }

    /**
     * @inheritDoc
     */
    public function getConfigValue(string $xpath, $storeId = null): mixed
    {
        return $this->config->getValue(
            self::XPATH_BASE . $xpath,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $storeId
        );
    }

    /**
     * @inheritDoc
     */
    public function getConfigFlag(string $xpath, $storeId = null): bool
    {
        return $this->config->isSetFlag(
            self::XPATH_BASE . $xpath,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $storeId
        );
    }

    /**
     * @inheritDoc
     */
    public function isEnabled(): bool
    {
        return $this->getConfigFlag(
            self::XPATH_MODULE_ENABLED,
            $this->storeConfigResolver->getStoreId()
        );
    }

    /**
     * @inheritDoc
     */
    public function isDebugEnabled(): bool
    {
        return $this->isEnabled() && $this->getConfigFlag(
            self::XPATH_DEBUG_ENABLED,
            $this->storeConfigResolver->getStoreId()
        );
    }

    /**
     * @inheritDoc
     */
    public function isProductionModeEnabled(): bool
    {
        return $this->getConfigValue(
            self::XPATH_MODULE_MODE,
            $this->storeConfigResolver->getStoreId()
        ) === self::MODE_PRODUCTION_VALUE;
    }

    /**
     * @inheritDoc
     */
    public function getProductionApiKey(): string
    {
        return $this->decryptCredential(
            $this->getConfigValue(
                self::XPATH_PRODUCTION_API_KEY,
                $this->storeConfigResolver->getStoreId()
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function getProductionEndpointUrl(): string
    {
        return $this->getConfigValue(
            self::XPATH_PRODUCTION_ENDPOINT_URL,
            $this->storeConfigResolver->getStoreId()
        );
    }

    /**
     * @inheritDoc
     */
    public function getPromotionsProductionEndpointUrl(): string
    {
        return $this->getConfigValue(
            self::XPATH_PROMOTIONS_PRODUCTION_ENDPOINT_URL,
            $this->storeConfigResolver->getStoreId()
        );
    }

    /**
     * @inheritDoc
     */
    public function isSandboxModeEnabled(): bool
    {
        return $this->getConfigValue(
            self::XPATH_MODULE_MODE,
                $this->storeConfigResolver->getStoreId()
            ) === self::MODE_SANDBOX_VALUE;
    }

    /**
     * @inheritDoc
     */
    public function isMockModeEnabled(): bool
    {
        return $this->getConfigValue(
            self::XPATH_MODULE_MODE,
            $this->storeConfigResolver->getStoreId()
        ) === self::MODE_MOCK_VALUE;
    }

    /**
     * @inheritDoc
     */
    public function getMockScenario(): string
    {
        return trim((string) $this->getConfigValue(
            self::XPATH_MOCK_SCENARIO,
            $this->storeConfigResolver->getStoreId()
        ));
    }

    /**
     * @inheritDoc
     */
    public function getSandboxApiKey(): string
    {
        return $this->decryptCredential(
            $this->getConfigValue(
                self::XPATH_SANDBOX_API_KEY,
                $this->storeConfigResolver->getStoreId()
            )
        );
    }

    /**
     * Credentials are stored through an `obscure` backend model, so the raw value is ciphertext.
     *
     * Magento's encryptor returns an empty string for anything it cannot decrypt, which would
     * silently send an empty Authorization header. A value that does not carry the `<version>:<key>:`
     * prefix was stored in the clear by an earlier release and is passed through unchanged, so
     * adding this call cannot break an instance whose key was pasted before the field was obscured.
     *
     * @param mixed $value
     *
     * @return string
     */
    private function decryptCredential($value): string
    {
        $value = (string) $value;

        if ($value === '' || !preg_match('/^\d+:\d+:/', $value)) {
            return $value;
        }

        return (string) $this->encryptor->decrypt($value);
    }

    public function getSandboxEndpointUrl(): string
    {
        return $this->getConfigValue(
            self::XPATH_SANDBOX_ENDPOINT_URL,
            $this->storeConfigResolver->getStoreId()
        );
    }

    public function getPromotionsSandboxEndpointUrl(): string
    {
        return $this->getConfigValue(
            self::XPATH_PROMOTIONS_SANDBOX_ENDPOINT_URL,
            $this->storeConfigResolver->getStoreId()
        );
    }

    /**
     * @inheritDoc
     */
    public function getConfiguredCcTypes(): string
    {
        return $this->getConfigValue(
            self::XPATH_CREDIT_CARD_TYPES,
            $this->storeConfigResolver->getStoreId()
        );
    }

    /**
     * @inheritDoc
     */
    public function getDocumentTypes(): array
    {
        $selected = $this->getConfigValue(
            self::XPATH_DOCUMENT_TYPES,
            $this->storeConfigResolver->getStoreId()
        );

        $selected = explode(',', $selected);
        $values = [];

        foreach ($selected as $key => $value) {
            $values[$value] = $value;
        }

        return $values;
    }

    /**
     * @inheritDoc
     */
    public function getOrderStatus(): string
    {
        return $this->getConfigValue(
            self::XPATH_ORDER_STATUS,
            $this->storeConfigResolver->getStoreId()
        );
    }

    /**
     * @inheritDoc
     */
    public function getTerminalSystem(): string
    {
        return $this->getConfigValue(
            self::XPATH_TERMINAL_SYSTEM,
            $this->storeConfigResolver->getStoreId()
        );
    }

    /**
     * @inheritDoc
     */
    public function getApiVersion(): string
    {
        return $this->getConfigValue(
            self::XPATH_API_VERSION,
            $this->storeConfigResolver->getStoreId()
        );
    }

    /**
     * @inheritDoc
     */
    public function getApiSslIsActive(): bool
    {
        return $this->getConfigFlag(
            self::XPATH_API_SSL_IS_ACTIVE,
            $this->storeConfigResolver->getStoreId()
        );
    }

    /**
     * @inheritDoc
     */
    public function getApiSslVersion(): int
    {
        $value = (int) $this->getConfigValue(
            self::XPATH_API_SSL_VERSION,
            $this->storeConfigResolver->getStoreId()
        );

        return isset(SSLVersionsInterface::SSL_VERSIONS_OPTIONS_LIST[$value])
            ? $value
            : CURL_SSLVERSION_TLSv1_2;
    }

    /**
     * @inheritDoc
     */
    public function getApiCredential(): string
    {
        return $this->isProductionModeEnabled()
            ? $this->getProductionApiKey()
            : $this->getSandboxApiKey();
    }

    /**
     * @inheritDoc
     */
    public function getApiEndpointUrl(): string
    {
        $url = $this->isProductionModeEnabled()
            ? $this->getProductionEndpointUrl()
            : $this->getSandboxEndpointUrl();

        return $url . '/'. $this->getApiVersion();
    }

    /**
     * @inheritDoc
     */
    public function getPromotionsApiEndpointUrl(): string
    {
        $url = $this->isProductionModeEnabled()
            ? $this->getPromotionsProductionEndpointUrl()
            : $this->getPromotionsSandboxEndpointUrl();

        return $url;
    }

    public function getPromotionsCredentials(): array
    {
        $marketplace = $this->getConfigValue(
            self::XPATH_PROMOTIONS_MARKETPLACE,
            $this->storeConfigResolver->getStoreId()
        );

        $account = $this->getConfigValue(
            self::XPATH_PROMOTIONS_ACCOUNT,
            $this->storeConfigResolver->getStoreId()
        );

        return [$marketplace, $account];
    }

    /**
     * Whether to display each installment price amount within dropdown
     *
     * @return bool
     */
    public function isDisplayInstallmentPriceEnabled(): bool
    {
        return $this->getConfigFlag(
            self::XPATH_DISPLAY_INSTALLMENTS_PRICE,
            $this->storeConfigResolver->getStoreId()
        );
    }

    /**
     * @return bool
     */
    public function isInstallmentsFilterEnabled(): bool
    {
        return $this->getConfigFlag(
            self::XPATH_INSTALLMENTS_FILTER_ENABLED
        );
    }

    /**
     * @return array
     */
    public function getInstallmentsFilterConfiguration(): array
    {
        $value = (string) $this->getConfigValue(
            self::XPATH_INSTALLMENTS_FILTER_CONFIGURATION
        );

        $results = [];

        // if config is removed, `null` comes
        if (!$value) {
            return $results;
        }

        $data = $this->serializer->unserialize($value);

        foreach ($data as $config) {
            $results[] = [
                // cast to float for later compare to Quote Grant Total
                'minimum' => (float) $config['minimum'],
                // converts into an `int[]`
                'installments' => array_map('intval', explode(',', $config['installments']))
            ];
        }

        // rearrange, in case configuration isn't sorted by min total
        if (!empty($results)) {
            usort($results, function ($a, $b) {
                return $a['minimum'] - $b['minimum'];
            });
        }

        return $results;
    }

    /**
     * @inheritDoc
     */
    public function getPromotionsCacheLifetime(): int
    {
        return (int) $this->getConfigValue(
            self::XPATH_PROMOTIONS_CACHE_LIFETIME,
            $this->storeConfigResolver->getStoreId()
        );
    }

    /**
     * @inheritDoc
     */
    public function isAntifraudEnabled(): bool
    {
        return $this->getConfigFlag(
            self::XPATH_ANTIFRAUD_ENABLED,
            $this->storeConfigResolver->getStoreId()
        );
    }

    /**
     * @inheritDoc
     */
    public function getAntifraudWindow(): int
    {
        $value = (int) $this->getConfigValue(
            self::XPATH_ANTIFRAUD_WINDOW,
            $this->storeConfigResolver->getStoreId()
        );

        return $value > 0 ? $value : 60;
    }

    /**
     * @inheritDoc
     */
    public function getAntifraudThreshold(string $dimension): int
    {
        if (!isset(self::ANTIFRAUD_THRESHOLD_PATHS[$dimension])) {
            return 0;
        }

        return (int) $this->getConfigValue(
            self::ANTIFRAUD_THRESHOLD_PATHS[$dimension],
            $this->storeConfigResolver->getStoreId()
        );
    }

    /**
     * @inheritDoc
     */
    public function getAntifraudStoreBreaker(): int
    {
        return (int) $this->getConfigValue(
            self::XPATH_ANTIFRAUD_STORE_BREAKER,
            $this->storeConfigResolver->getStoreId()
        );
    }

    /**
     * @inheritDoc
     */
    public function isAntifraudIpEnabled(): bool
    {
        return $this->getConfigFlag(
            self::XPATH_ANTIFRAUD_USE_IP,
            $this->storeConfigResolver->getStoreId()
        );
    }
}
