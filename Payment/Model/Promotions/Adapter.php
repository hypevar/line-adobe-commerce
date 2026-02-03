<?php
/**
 * Copyright © 2024 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Model\Promotions;

use Line\Payment\Api\Data\ConfigInterface;
use Line\Payment\Model\Promotions\Connector;

/**
 * Adapter class for easy access to Gateway Promotions
 */
class Adapter
{
    protected ConfigInterface $configuration;
    protected Connector $connector;
    protected DataConverter $converter;
    protected $credentials = [];

    /**
     * @param ConfigInterface
     * @param Connector
     * @param DataConverter
     */
    public function __construct(
        ConfigInterface $module,
        Connector $connector,
        DataConverter $convert
    ) {
        $this->configuration = $module;
        $this->connector = $connector;
        $this->converter = $convert;

        $this->setupConnector();
    }

    /**
     * Initializes the Connector with credentials
     *
     * @return $this
     */
    protected function setupConnector()
    {
        $key = $this->configuration->getApiCredential();
        $base = $this->configuration->getPromotionsApiEndpointUrl();
        $agent = 'Magento Line Payment Gateway ' . ConfigInterface::MODULE_VERSION;
        $version = $this->configuration->getApiVersion();

        $this->connector->setBaseUrl($base)
            ->setAuthorizationKey($key)
            ->setUserAgent($agent)
            ->setApiVersion($version);

        return $this;
    }

    /**
     * @param string $url
     * @param array $params
     * @param bool $convert
     *
     * @return array
     */
    public function get(string $url, array $params = [], bool $convert = false): array
    {
        list(
            $marketplace,
            $account
        ) = $this->configuration->getPromotionsCredentials();

        $url = sprintf(
            $url,
            $marketplace,
            $account
        );

        $response = $this->connector->get($url, $params);

        if ($convert) {
            // build response object
            /** @var array $response */
            $response = $this->converter->convert($response);
        }

        return $response;
    }
}
