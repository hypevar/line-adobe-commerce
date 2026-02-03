<?php
/**
 * Copyright © 2024 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\VerifiedPurchase\Model;

use Line\VerifiedPurchase\Api\Data\ConfigInterface;
use Line\VerifiedPurchase\Model\StoreConfigResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class to access configuration option's values
 */
class Config implements ConfigInterface
{
    /**
     * @var StoreConfigResolver
     */
    private $storeConfigResolver;

    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @param StoreConfigResolver $storeConfigResolver
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        StoreConfigResolver $storeConfigResolver,
        ScopeConfigInterface $scopeConfig,
    ) {
        $this->storeConfigResolver = $storeConfigResolver;
        $this->config = $scopeConfig;
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

    /**
     * @inheritDoc
     */
    public function getSandboxEndpointUrl(): string
    {
        return $this->getConfigValue(
            self::XPATH_SANDBOX_ENDPOINT_URL,
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

        return $url . '/' . $this->getApiVersion();
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
    public function getVerificationMode(): string
    {
        return $this->getConfigValue(
            self::XPATH_VERIFICATION_MODE,
            $this->storeConfigResolver->getStoreId()
        );
    }

    /**
     * @inheritDoc
     */
    public function getCreditCardSummaryDescription(): string
    {
        return $this->getConfigValue(
            self::XPATH_CREDIT_CARD_SUMMARY_DESCRIPTION,
            $this->storeConfigResolver->getStoreId()
        );
    }

    /**
     * @inheritDoc
     */
    public function getTimeUnit(): string
    {
        return $this->getConfigValue(
            self::XPATH_TIME_UNIT,
            $this->storeConfigResolver->getStoreId()
        );
    }

    /**
     * @inheritDoc
     */
    public function getTimeAmount(): int
    {
        return (int) $this->getConfigValue(
            self::XPATH_TIME_AMOUNT,
            $this->storeConfigResolver->getStoreId()
        );
    }

    /**
     * @inheritDoc
     */
    public function getMaxTries(): int
    {
        return (int) $this->getConfigValue(
            self::XPATH_MAX_TRIES,
            $this->storeConfigResolver->getStoreId()
        );
    }

    /**
     * @inheritDoc
     */
    public function getOrderStatusPending(): string
    {
        return $this->getConfigValue(
            self::XPATH_ORDER_STATUS_PENDING,
            $this->storeConfigResolver->getStoreId()
        );
    }

    /**
     * @inheritDoc
     */
    public function getOrderStatusCompleted(): string
    {
        return $this->getConfigValue(
            self::XPATH_ORDER_STATUS_COMPLETED,
            $this->storeConfigResolver->getStoreId()
        );
    }

    /**
     * @inheritDoc
     */
    public function getOrderStatusFailed(): string
    {
        return $this->getConfigValue(
            self::XPATH_ORDER_STATUS_FAILED,
            $this->storeConfigResolver->getStoreId()
        );
    }

    /**
     * @inheritDoc
     */
    public function getEmailSender(): string
    {
        return $this->getConfigValue(
            self::XPATH_EMAIL_SENDER,
            $this->storeConfigResolver->getStoreId()
        );
    }

    /**
     * @inheritDoc
     */
    public function getEmailProcessPendingTemplate(): string
    {
        return $this->getConfigValue(
            self::XPATH_EMAIL_TEMPLATE_PENDING,
            $this->storeConfigResolver->getStoreId()
        );
    }

    /**
     * @inheritDoc
     */
    public function getEmailProcessCompleteTemplate(): string
    {
        return $this->getConfigValue(
            self::XPATH_EMAIL_TEMPLATE_COMPLETED,
            $this->storeConfigResolver->getStoreId()
        );
    }

    /**
     * @inheritDoc
     */
    public function getEmailProcessCanceledTemplate(): string
    {
        return $this->getConfigValue(
            self::XPATH_EMAIL_TEMPLATE_CANCELED,
            $this->storeConfigResolver->getStoreId()
        );
    }
}
