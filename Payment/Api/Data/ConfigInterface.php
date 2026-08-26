<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Api\Data;

/**
 * Interface exposing Module Configuration options
 */
interface ConfigInterface
{
    /**#@+
     * @var string
     * @access public
     */
    public const API_VERSION = 'v1';
    public const MODULE_VERSION = '0.1.0';
    public const PAYMENT_METHOD_CODE = 'linepayment';

    public const XPATH_BASE = 'payment/linepayment/';
    public const XPATH_MODULE_ENABLED = 'active';
    public const XPATH_DEBUG_ENABLED = 'debug_enabled';

    public const XPATH_MODULE_MODE = 'module_mode';
    public const MODE_SANDBOX_VALUE = 'sandbox';
    public const MODE_PRODUCTION_VALUE = 'production';
    public const MODE_MOCK_VALUE = 'mock';

    public const XPATH_MOCK_SCENARIO = 'mock_scenario';

    public const CREDENTIALS_PUBLIC_KEY = 'public_key';
    public const CREDENTIALS_PRIVATE_KEY = 'private_key';

    public const XPATH_SANDBOX_API_KEY = 'sandbox_api_key';
    public const XPATH_PRODUCTION_API_KEY = 'production_api_key';

    public const XPATH_SANDBOX_ENDPOINT_URL = 'sandbox_url';
    public const XPATH_PRODUCTION_ENDPOINT_URL = 'production_url';

    public const XPATH_DOCUMENT_TYPES = 'document_types';
    public const XPATH_ORDER_STATUS = 'order_status';
    public const XPATH_TERMINAL_SYSTEM = 'terminal_system';

    public const XPATH_PROMOTIONS_SANDBOX_ENDPOINT_URL = 'promotions_sandbox_url';
    public const XPATH_PROMOTIONS_PRODUCTION_ENDPOINT_URL = 'promotions_production_url';
    public const XPATH_PROMOTIONS_MARKETPLACE = 'promotions_marketplace';
    public const XPATH_PROMOTIONS_ACCOUNT = 'promotions_account';

    public const XPATH_INSTALLMENTS_FILTER_ENABLED = 'installments_filter_enabled';
    public const XPATH_INSTALLMENTS_FILTER_CONFIGURATION = 'installments_filter_configuration';

    public const XPATH_CREDIT_CARD_TYPES = 'credit_card_types';
    public const XPATH_CCTYPES_MAPPER = 'cctypes_mapper';
    public const XPATH_DISPLAY_INSTALLMENTS_PRICE = 'display_installments_price';
    public const XPATH_USE_CVV = 'usecvv';

    public const XPATH_API_VERSION = 'api_version';
    public const XPATH_API_SSL_IS_ACTIVE = 'api_ssl_is_active';
    public const XPATH_API_SSL_VERSION = 'api_ssl_version';

    public const XPATH_PROMOTIONS_CACHE_LIFETIME = 'promotions_cache_lifetime';

    public const XPATH_ANTIFRAUD_ENABLED = 'antifraud_enabled';
    public const XPATH_ANTIFRAUD_WINDOW = 'antifraud_window';
    public const XPATH_ANTIFRAUD_MAX_DECLINES_CARD = 'antifraud_max_declines_card';
    public const XPATH_ANTIFRAUD_MAX_DECLINES_BIN = 'antifraud_max_declines_bin';
    public const XPATH_ANTIFRAUD_MAX_DECLINES_QUOTE = 'antifraud_max_declines_quote';
    public const XPATH_ANTIFRAUD_MAX_DECLINES_EMAIL = 'antifraud_max_declines_email';
    public const XPATH_ANTIFRAUD_MAX_DECLINES_CUSTOMER = 'antifraud_max_declines_customer';
    public const XPATH_ANTIFRAUD_MAX_DECLINES_IP = 'antifraud_max_declines_ip';
    public const XPATH_ANTIFRAUD_STORE_BREAKER = 'antifraud_store_breaker';
    public const XPATH_ANTIFRAUD_USE_IP = 'antifraud_use_ip';
    /**#@-*/

    /**
     * Return module's configuration value
     *
     * @param string $xpath
     * @param null|int|string $storeId
     *
     * @return mixed
     */
    public function getConfigValue(string $xpath, $storeId): mixed;

    /**
     * Returns a module's configuration flag value
     *
     * @param string $xpath
     * @param null|int|string $storeId
     *
     * @return bool
     */
    public function getConfigFlag(string $xpath, $storeId): bool;

    /**
     * Whether module is enabled or not
     *
     * @return bool
     */
    public function isEnabled(): bool;

    /**
     * Whether module's debug log is enabled or not
     *
     * @return bool
     */
    public function isDebugEnabled(): bool;

    /**
     * Whether Sandbox mode is the current mode
     *
     * @return bool
     */
    public function isProductionModeEnabled(): bool;

    /**
     * Returns Production api key
     *
     * @return string
     */
    public function getProductionApiKey(): string;

    /**
     * @return string
     */
    public function getProductionEndpointUrl(): string;

    /**
     * Whether Sandbox mode is the current mode
     *
     * @return bool
     */
    public function isSandboxModeEnabled(): bool;

    /**
     * Whether gateway calls are answered from committed fixtures instead of the network.
     *
     * @return bool
     */
    public function isMockModeEnabled(): bool;

    /**
     * Fixture scenario to force, or an empty string to pick one at random per call.
     *
     * @return string
     */
    public function getMockScenario(): string;

    /**
     * Returns Sandbox Api key
     *
     * @return string
     */
    public function getSandboxApiKey(): string;

    /**
     * @return string
     */
    public function getSandboxEndpointUrl(): string;

    /**
     * List of comma separated Credit Card type values
     *
     * @return string
     */
    public function getConfiguredCcTypes(): string;

    /**
     * Returns configured document types
     *
     * @return array
     */
    public function getDocumentTypes(): array;

    /**
     * Configured Order Status to be set, once payment process has finished
     *
     * @return string
     */
    public function getOrderStatus(): string;

    /**
     * Terminal System value to be sent into the payload
     *
     * @return string
     */
    public function getTerminalSystem(): string;

    /**
     * Returns base API url based on environment and api-version configurations
     *
     * @return string
     */
    public function getApiEndpointUrl(): string;

    /**
     * Returns the right api key, based on the configured environment type
     *
     * @return string
     */
    public function getApiCredential(): string;

    /**
     * Returns which version of the API must be used
     *
     * @return string
     */
    public function getApiVersion(): string;

    /**
     * Which SSL type and version does connections must use
     *
     * @return int
     */
    public function getApiSslVersion(): int;

    /**
     * Whether SSL is enabled or not
     *
     * @return bool
     */
    public function getApiSslIsActive(): bool;

    /**
     * @return string
     */
    public function getPromotionsSandboxEndpointUrl(): string;

    /**
     * @return string
     */
    public function getPromotionsProductionEndpointUrl(): string;

    /**
     * Returns Promotions API url based on environment
     *
     * @return string
     */
    public function getPromotionsApiEndpointUrl(): string;

    /**
     * Value pair of marketplace and account for Promotions API
     *
     * @return array
     */
    public function getPromotionsCredentials(): array;

    /**
     * Whether to display each installment price amount within dropdown
     *
     * @return bool
     */
    public function isDisplayInstallmentPriceEnabled(): bool;

    /**
     * @return bool
     */
    public function isInstallmentsFilterEnabled(): bool;

    /**
     * @return array
     */
    public function getInstallmentsFilterConfiguration(): array;

    /**
     * How long a promotions payload stays cached, in seconds
     *
     * @return int
     */
    public function getPromotionsCacheLifetime(): int;

    /**
     * Whether the card-testing throttle is active
     *
     * @return bool
     */
    public function isAntifraudEnabled(): bool;

    /**
     * Length of the throttle counting window, in minutes
     *
     * @return int
     */
    public function getAntifraudWindow(): int;

    /**
     * Declines tolerated within the window for the given dimension
     *
     * @param string $dimension
     *
     * @return int
     */
    public function getAntifraudThreshold(string $dimension): int;

    /**
     * Store-wide decline count that flips the throttle into strict mode
     *
     * @return int
     */
    public function getAntifraudStoreBreaker(): int;

    /**
     * Whether the remote address is used as a throttle dimension
     *
     * @return bool
     */
    public function isAntifraudIpEnabled(): bool;
}
