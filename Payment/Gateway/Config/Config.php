<?php
/**
 * Copyright © Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Payment\Gateway\Config\Config as GatewayConfig;
use Line\Payment\Api\Data\ConfigInterface;
use Line\Payment\Model\StoreConfigResolver;

/**
 * Payment Configuration provider
 */
class Config extends GatewayConfig
{
    const XPATH_MODULE_ENABLED = ConfigInterface::XPATH_MODULE_ENABLED;
    const XPATH_MODULE_MODE = ConfigInterface::XPATH_MODULE_MODE;

    const KEY_ENVIRONMENT = 'environment';
    const MODE_SANDBOX = ConfigInterface::MODE_SANDBOX_VALUE;
    const MODE_PRODUCTION = ConfigInterface::MODE_PRODUCTION_VALUE;

    const XPATH_SANDBOX_API_KEY = ConfigInterface::XPATH_SANDBOX_API_KEY;
    const XPATH_PRODUCTION_API_KEY = ConfigInterface::XPATH_PRODUCTION_API_KEY;

    const XPATH_USE_CVV = ConfigInterface::XPATH_USE_CVV;
    const XPATH_CREDIT_CARD_TYPES = ConfigInterface::XPATH_CREDIT_CARD_TYPES;
    const XPATH_CC_TYPES_MAPPER = ConfigInterface::XPATH_CCTYPES_MAPPER;

    /**
     * @var StoreConfigResolver
     */
    private $storeConfigResolver;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    private ConfigInterface $moduleConfig;

    /**
     * @param StoreConfigResolver $storeConfigResolver
     * @param ScopeConfigInterface $scopeConfig
     * @param ConfigInterface $moduleConfig
     * @param null|string $methodCode
     * @param string $pathPattern
     * @param Json|null $serializer
     */
    public function __construct(
        StoreConfigResolver $storeConfigResolver,
        ScopeConfigInterface $scopeConfig,
        ConfigInterface $moduleConfig,
        $methodCode = null,
        $pathPattern = self::DEFAULT_PATH_PATTERN,
        Json $serializer = null
    ) {
        parent::__construct($scopeConfig, $methodCode, $pathPattern);

        $this->scopeConfig = $scopeConfig;
        $this->storeConfigResolver = $storeConfigResolver;
        $this->moduleConfig = $moduleConfig;
        $this->serializer = $serializer ?: ObjectManager::getInstance()
            ->get(Json::class);
    }

    /**
     * Retrieve available credit card types
     *
     * @throws InputException
     * @throws NoSuchEntityException
     *
     * @return array
     */
    public function getAvailableCardTypes(): array
    {
        $types = $this->getValue(
            ConfigInterface::XPATH_CREDIT_CARD_TYPES,
            $this->storeConfigResolver->getStoreId()
        );

        return !empty($types)
            ? explode(',', $types)
            : [];
    }

    /**
     * Retrieve mapper between Magento and Gateway card types
     *
     * @throws InputException
     * @throws NoSuchEntityException
     *
     * @return array
     */
    public function getCcTypesMapper(): array
    {
        $mapper = $this->getValue(
            ConfigInterface::XPATH_CCTYPES_MAPPER,
            $this->storeConfigResolver->getStoreId()
        );

        $result = json_decode($mapper, true);

        return is_array($result) ? $result : [];
    }

    /**
     * @param string $type
     *
     * @return string
     */
    public function getMagentoCcType(string $type): string
    {
        return array_search(
            $type,
            $this->getCcTypesMapper()
        ) ?? '';
    }

    /**
     * Checks if CVV field is enabled
     *
     * @throws InputException
     * @throws NoSuchEntityException
     *
     * @return bool
     */
    public function isCvvEnabled(): bool
    {
        return (bool) $this->getValue(
            self::XPATH_USE_CVV,
            $this->storeConfigResolver->getStoreId()
        );
    }

    /**
     * @return bool
     */
    public function isCvvEnabledVault(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function isActive(int $storeId = null)
    {
        return (bool) $this->getValue(
            self::XPATH_MODULE_ENABLED,
            $storeId ?? $this->storeConfigResolver->getStoreId()
        );
    }

    /**
     * @return bool
     */
    public function isProductionModeEnabled(): bool
    {
        $storeId = $this->storeConfigResolver->getStoreId();

        return $this->getValue(
            self::XPATH_MODULE_MODE,
            $storeId
        ) === self::MODE_PRODUCTION;
    }

    /**
     * @return bool
     */
    public function isSandboxModeEnabled(): bool
    {
        return $this->getValue(
            self::XPATH_MODULE_MODE,
            $this->storeConfigResolver->getStoreId()
        ) === self::MODE_SANDBOX;
    }

    /**
     * @return string
     */
    public function getProductionApiKey(): string
    {
        return $this->getValue(
            self::XPATH_PRODUCTION_API_KEY,
            $this->storeConfigResolver->getStoreId()
        );
    }

    /**
     * @return string
     */
    public function getSandboxApiKey(): string
    {
        return $this->getValue(
            self::XPATH_SANDBOX_API_KEY,
            $this->storeConfigResolver->getStoreId()
        );
    }

    /**
     * Returns credentials based on which current env mode
     *
     * @return string
     */
    public function getApiCredentials(): string
    {
        if ($this->isProductionModeEnabled()) {
            return $this->getProductionApiKey();
        }

        return $this->getSandboxApiKey();
    }

    /**
     * Get current env mode
     *
     * @throws InputException
     * @throws NoSuchEntityException
     *
     * @return string
     */
    public function getMode(): string
    {
        return $this->getValue(
            self::XPATH_MODULE_MODE,
            $this->storeConfigResolver->getStoreId()
        );
    }

    /**
     * List of comma separated Credit Card type values
     *
     * @return string
     */
    public function getConfiguredCcTypes(): string
    {
        return $this->getValue(
            self::XPATH_CREDIT_CARD_TYPES,
            $this->storeConfigResolver->getStoreId()
        );
    }
}
