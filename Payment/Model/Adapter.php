<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Model;

use Line\Payment\Api\Data\ConfigInterface;
use Line\Payment\Api\Data\ConnectorInterface;
use Line\Payment\Api\Request\AttributeInterface;
use Line\Payment\Model\Client\DataConverter;
use Line\Payment\Api\ResponseInterface;
use Psr\Log\LoggerInterface;

class Adapter
{
    protected ConfigInterface $configuration;
    protected ConnectorInterface $connector;
    protected DataConverter $dataConverter;
    protected LoggerInterface $logger;

    protected $credentials = [];

    /**
     * @param ConfigInterface
     * @param ConnectorInterface
     */
    public function __construct(
        ConfigInterface $module,
        ConnectorInterface $connector,
        DataConverter $dataConverter,
        LoggerInterface $logger
    ) {
        $this->configuration = $module;
        $this->connector = $connector;
        $this->dataConverter = $dataConverter;
        $this->logger = $logger;

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
        $base = $this->configuration->getApiEndpointUrl();
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
     * @return ResponseInterface|array
     */
    public function call(string $method, string $url, array $params, $convert = true)
    {
        $response = $this->connector->{$method}($url, $params);

        return $convert
            ? $this->dataConverter->convert($response)
            : $response;
    }

    /**
     * Executes a Payment operation
     *
     * @param array $attributes
     * @return array|ResponseInterface
     */
    public function sale(array $attributes)
    {
        // log payload to send
        $this->logMaskedPayload($attributes);

        return $this->call('post', '/creditcard/autorizacion', $attributes);
    }

    /**
     * Executes a partial refund against a specific Payment
     *
     * @param string $id Id of the payment to be refunded
     */
    public function refund(string $id)
    {
        return $this->call('post', '/creditcard/anulacion/' . $id, []);
    }

    /**
     * Mask data and log payload
     *
     * @param $data
     * @return void
     */
    protected function logMaskedPayload($data)
    {
        if (!$this->configuration->isDebugEnabled()) {
            return;
        }

        // mask cvv data
        $data[AttributeInterface::FIELD_CREDIT_CARD_CVV] = 'xxx';

        // mask card number, get only bin
        $card = (string) ($data[AttributeInterface::FIELD_CREDIT_CARD_NUMBER] ?? '');
        $maskedCard = substr($card, 0, 6);
        $maskedCard .= str_repeat('x', max(0, strlen($card) - 6));
        $data[AttributeInterface::FIELD_CREDIT_CARD_NUMBER] = $maskedCard;

        foreach ([
            AttributeInterface::FIELD_CREDIT_CARD_EXPIRATION_DATE,
            AttributeInterface::FIELD_CARDHOLDER_FULLNAME,
            AttributeInterface::FIELD_CARDHOLDER_DOCUMENT_NUMBER,
            AttributeInterface::FIELD_CARDHOLDER_EMAIL,
            AttributeInterface::FIELD_CUSTOMER_IP,
            AttributeInterface::FIELD_TRACK_I,
            AttributeInterface::FIELD_TRACK_II
        ] as $field) {
            if (isset($data[$field]) && $data[$field] !== '') {
                $data[$field] = '***';
            }
        }

        $this->logger->debug('Payload to execute payment', ['data' => $data]);
    }

}
