<?php
/**
 * Copyright © 2024 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Model\Ui;

use Line\Payment\Api\Data\ConfigInterface;
use Line\Payment\Api\Data\Checkout\AttributeProviderInterface;
use Line\Payment\Api\GetEmittersActionInterface;
use Line\Payment\Gateway\Config\Config;
use Line\Payment\Model\Config\Source\CreditCardMethods;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Asset\Source;
use Magento\Payment\Model\CcConfig as PaymentConfig;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;

/**
 * Provides information required for rendering the Checkout Payment Form
 */
class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var string
     */
    private const CODE = ConfigInterface::PAYMENT_METHOD_CODE;

    /**
     * @var array
     */
    private $icons = [];

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var ConfigInterface
     */
    private ConfigInterface $configuration;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @var PaymentConfig
     */
    protected PaymentConfig $paymentConfig;

    /**
     * @var Source
     */
    protected Source $assetSource;

    /**
     * @var UrlInterface
     */
    protected UrlInterface $urlBuilder;

    /**
     * @var GetEmittersActionInterface
     */
    protected GetEmittersActionInterface $emittersAction;

    /**
     * @var CreditCardMethods
     */
    protected CreditCardMethods $creditCardMethods;

    /**
     *
     * @param Config $config
     * @param ConfigInterface $configuration
     * @param StoreManagerInterface $storeManager
     * @param PaymentConfig $paymentConfig
     * @param Source $assetSource
     * @param UrlInterface $urlBuilder
     * @param GetEmittersActionInterface $emittersAction
     * @param CreditCardMethods $creditCardMethods
     */
    public function __construct(
        Config $config,
        ConfigInterface $configuration,
        StoreManagerInterface $storeManager,
        PaymentConfig $paymentConfig,
        Source $assetSource,
        UrlInterface $urlBuilder,
        GetEmittersActionInterface $emittersAction,
        CreditCardMethods $creditCardMethods
    ) {
        $this->config = $config;
        $this->configuration = $configuration;
        $this->storeManager = $storeManager;
        $this->paymentConfig = $paymentConfig;
        $this->assetSource = $assetSource;
        $this->urlBuilder = $urlBuilder;
        $this->emittersAction = $emittersAction;
        $this->creditCardMethods = $creditCardMethods;
    }

    /**
     * @inheritDoc
     */
    public function getConfig(): array
    {
        return $this->configuration->isEnabled()
            ? [
                'payment' => [
                    self::CODE => [
                        'code' => self::CODE,
                        AttributeProviderInterface::IS_ACTIVE => $this->config->isActive(),
                        AttributeProviderInterface::ENV => [
                            'is_sandbox' => $this->isSandboxMode()
                        ],
                        AttributeProviderInterface::AVAILABLE_TYPES => $this->getCcAvailableTypes(),
                        AttributeProviderInterface::MONTHS => $this->getCcMonths(),
                        AttributeProviderInterface::YEARS => $this->getCcYears(),
                        AttributeProviderInterface::HAS_VERIFICATION => $this->hasVerification(),
                        AttributeProviderInterface::CVV_IMAGE_URL => $this->getCvvImageUrl(),
                        AttributeProviderInterface::ICONS => $this->getIcons(),
                        AttributeProviderInterface::DOCUMENT_TYPES => $this->getAvailableDocumentTypes(),
                        AttributeProviderInterface::PROMOTION_BY_BIN_ACTION_URL => $this->getPromotionsByBinActionUrl(),
                        AttributeProviderInterface::PROMOTION_ACTION_URL => $this->getPromotionsActionUrl(),
                        // AttributeProviderInterface::EMITTERS => $this->getEmitters(),
                        AttributeProviderInterface::INSTALLMENTS => $this->getInstallmentsConfig(),
                        AttributeProviderInterface::CREDIT_CARD_METHOD => $this->getCreditCardMethod(),
                    ]
                ]
            ]
            : [];
    }

    /**
     * Retrieve expiration months list
     *
     * @return array
     */
    protected function getCcMonths()
    {
        return $this->paymentConfig->getCcMonths();
    }

    /**
     * Retrieve expiration years list
     *
     * @return array
     */
    protected function getCcYears()
    {
        return $this->paymentConfig->getCcYears();
    }

    /**
     * Retrieve CVV tooltip image url
     *
     * @return string
     */
    protected function getCvvImageUrl()
    {
        return $this->paymentConfig->getCvvImageUrl();
    }

    /**
     * @TODO: refactor day/month/availableCcTypes methods
     *
     * @return array
     */
    public function getCcAvailableTypes()
    {
        $types = $this->paymentConfig->getCcAvailableTypes();
        $availableTypes = $this->config->getConfiguredCcTypes();

        if ($availableTypes) {
            $availableTypes = explode(',', $availableTypes);

            // @TODO ensure this loop is strictly necessary, otherwise return `$availableTypes` directly
            foreach (array_keys($types) as $code) {
                if (!in_array($code, $availableTypes)) {
                    unset($types[$code]);
                }
            }
        }
        return $types;
    }

    /**
     * Retrieve has verification configuration
     *
     * @return bool
     */
    protected function hasVerification()
    {
        return $this->paymentConfig->hasVerification();
    }

    /**
     *
     * @throws NoSuchEntityException
     *
     * @return bool
     */
    public function isSandboxMode()
    {
        return $this->config->isSandboxModeEnabled();
    }

    /**
     * @return string[]
     */
    public function getAvailableDocumentTypes(): array
    {
        return $this->configuration->getDocumentTypes();
    }

    /**
     * Get icons for available payment methods
     *
     * @return array
     */
    public function getIcons()
    {
        if (!empty($this->icons)) {
            return $this->icons;
        }

        /** @var string[] Credit Card Type codes */
        $types = $this->paymentConfig->getCcAvailableTypes();

        foreach ($types as $code => $label) {
            if (!array_key_exists($code, $this->icons)) {
                $asset = $this->paymentConfig->createAsset('Line_Payment::images/types/' . strtolower($code) . '.png');
                $placeholder = $this->assetSource->findSource($asset);

                if ($placeholder) {
                    list($width, $height) = getimagesize($asset->getSourceFile());
                    $this->icons[$code] = [
                        'url' => $asset->getUrl(),
                        'width' => $width,
                        'height' => $height,
                        'title' => __($label),
                    ];
                }
            }
        }

        return $this->icons;
    }

    public function getPromotionsByBinActionUrl(): string
    {
        return $this->urlBuilder->getUrl('line/ajax/bin', ['_secure' => true]);
    }

    public function getPromotionsActionUrl(): string
    {
        return $this->urlBuilder->getUrl('line/ajax/promotions', ['_secure' => true]);
    }

    // public function getEmitters(): array
    // {
    //     try {
    //         return [
    //             'error' => false,
    //             'result' => $this->emittersAction->get()
    //         ];

    //     } catch (\Exception $e) {
    //         return [
    //             'error' => true,
    //             'result' => [],
    //             'message' => $e->getMessage()
    //         ];
    //     }
    // }

    public function getInstallmentsConfig(): array
    {
        $isFilterEnabled = $this->configuration->isInstallmentsFilterEnabled();
        $filterConfiguration = $isFilterEnabled
            ? $this->configuration->getInstallmentsFilterConfiguration()
            : [];

        return [
            'displayPrice' => $this->configuration->isDisplayInstallmentPriceEnabled(),
            'filter' => [
                'enabled' => $isFilterEnabled,
                'config' => $filterConfiguration
            ]
        ];
    }

    /**
     * @return array
     */
    protected function getCreditCardMethod(): array
    {
        return $this->creditCardMethods->toOptionArray();
    }
}
