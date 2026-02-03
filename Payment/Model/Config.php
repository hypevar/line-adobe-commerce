<?php
/**
 * Copyright © 2024 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Line\Payment\Api\Data\ConfigInterface;
use Line\Payment\Model\StoreConfigResolver;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class for accessing module's configuration option values
 */
class Config implements ConfigInterface
{
    private StoreConfigResolver $storeConfigResolver;
    private ScopeConfigInterface $config;
    private Json $serializer;

    /**
     * @param StoreConfigResolver $storeConfigResolver
     * @param ScopeConfigInterface $scopeConfig
     * @param Json $serializer
     */
    public function __construct(
        StoreConfigResolver $storeConfigResolver,
        ScopeConfigInterface $scopeConfig,
        Json $serializer
    ) {
        $this->storeConfigResolver = $storeConfigResolver;
        $this->config = $scopeConfig;
        $this->serializer = $serializer;
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
        return $this->getConfigValue(
            self::XPATH_PRODUCTION_API_KEY,
            $this->storeConfigResolver->getStoreId()
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
    public function getSandboxApiKey(): string
    {
        return $this->getConfigValue(
            self::XPATH_SANDBOX_API_KEY,
            $this->storeConfigResolver->getStoreId()
        );
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
        return (int) $this->getConfigValue(
            self::XPATH_API_SSL_VERSION,
            $this->storeConfigResolver->getStoreId()
        );
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
}
