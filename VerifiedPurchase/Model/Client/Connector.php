<?php
/**
 * Copyright © 2024 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\VerifiedPurchase\Model\Client;

use Line\Payment\Api\Data\ConfigInterface;
use Line\VerifiedPurchase\Api\Data\ConnectorInterface;
use Line\VerifiedPurchase\Api\Data\ConfigInterface as VerifiedPurchaseConfigInterface;
use Line\VerifiedPurchase\Api\Verification\Gateway\Request\AttributeInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\HTTP\Client\CurlFactory;
use Psr\Log\LoggerInterface;

/**
 * Main entrypoint for connections against the service
 */
class Connector implements ConnectorInterface
{
    /**
     * Header's auth key to be included in every request
     * @var string
     */
    private string $authorization;

    /**
     * Api base url which includes: protocol, domain and api_version
     * @var string
     */
    private string $base_url;

    /**
     * Header's Api Version value
     * @var string
     */
    private string $api_version;

    /**
     * Header's User Agent value
     * @var string
     */
    private string $user_agent;

    /**
     * @var CurlFactory
     */
    protected CurlFactory $curlFactory;

    /**
     * @var ConfigInterface
     */
    protected ConfigInterface $configuration;

    /**
     * @var VerifiedPurchaseConfigInterface
     */
    protected VerifiedPurchaseConfigInterface $verifiedPurchaseConfiguration;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @var DataObjectHelper
     */
    protected DataObjectHelper $objectHelper;

    /**
     * Class constructor
     *
     * @param ConfigInterface $config
     * @param VerifiedPurchaseConfigInterface $verifiedPurchaseConfig
     * @param CurlFactory $curl
     * @param DataObjectHelper $objectHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        ConfigInterface $config,
        VerifiedPurchaseConfigInterface $verifiedPurchaseConfig,
        CurlFactory $curl,
        DataObjectHelper $objectHelper,
        LoggerInterface $logger
    ) {
        $this->configuration = $config;
        $this->verifiedPurchaseConfiguration = $verifiedPurchaseConfig;
        $this->curlFactory = $curl;
        $this->objectHelper = $objectHelper;
        $this->logger = $logger;
    }

    /**
     * Returns authorization key
     *
     * @return string
     */
    private function getAuthorizationKey(): string
    {
        return $this->authorization;
    }

    /**
     * @inheritDoc
     */
    public function setAuthorizationKey(string $value): ConnectorInterface
    {
        $this->authorization = $value;
        return $this;
    }

    /**
     * Returns User Agent for the request
     *
     * @return string
     */
    private function getUserAgent(): string
    {
        return $this->user_agent;
    }

    /**
     * @inheritDoc
     */
    public function setUserAgent(string $value): ConnectorInterface
    {
        $this->user_agent = $value;
        return $this;
    }

    /**
     * Returns Api version to be used
     *
     * @return string
     */
    private function getApiVersion(): string
    {
        return $this->api_version;
    }

    /**
     * @inheritDoc
     */
    public function setApiVersion(string $value): ConnectorInterface
    {
        $this->api_version = $value;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setBaseUrl(string $value): ConnectorInterface
    {
        $this->base_url = $value;
        return $this;
    }

    /**
     * Return base url of the external service
     *
     * @return string
     */
    private function getBaseUrl(): string
    {
        return $this->base_url;
    }

    /**
     * Executes a GET request
     *
     * @param string $path
     * @param array $body
     *
     * @return array
     */
    public function get($path, $body)
    {
        return $this->makeRequest(self::METHOD_GET, $path, $body);
    }

    /**
     * Executes a POST request
     *
     * @param string $path
     * @param array $body
     *
     * @return array
     */
    public function post($path, $body): array
    {
        return $this->makeRequest(self::METHOD_POST, $path, $body);
    }

    /**
     * Executes a request based on the given properties
     *
     * @param string $method http method
     * @param string $httpUri Endpoint uri
     * @param array $body request data
     *
     * @return mixed
     */
    protected function makeRequest(string $method, string $httpUri, array $body = [])
    {
        // Holds information for dump in debug log after execution
        $debug = [];

        try {
            // perform basic checks to ensure we're ready to build the request
            $this->validate();

            if ($this->verifiedPurchaseConfiguration->isDebugEnabled()) {
                $this->logger->debug('Connector Debug request', [
                    'method' => $method,
                    'http-uri' => $httpUri,
                    'body' => $this->mask($body)
                ]);
            }

            $requestUrl = $this->getBaseUrl() . $httpUri;

            /** @var Curl $request */
            $request = $this->curlFactory->create();

            // debug: method and url values
            $debug['method'] = $method;
            $debug['url'] = $requestUrl;

            // basic setup
            $authorization = self::HEADER_AUTH_KEY_NAME . ' ' . $this->getAuthorizationKey();
            $request->addHeader('Authorization', $authorization);
            $request->addHeader('Accept', 'application/json');
            $request->addHeader('Content-Type', 'application/json');
            $request->addHeader('User-Agent', $this->getUserAgent());
            $request->addHeader('X-ApiVersion', $this->getApiVersion());

            // Headers setup
            $request->setTimeout(self::REQUEST_TIMEOUT);

            // SSL configuration
            if ($this->configuration->getApiSslIsActive()) {
                $request->setOptions([
                    CURLOPT_SSLVERSION => $this->configuration->getApiSslVersion(),
                    CURLOPT_SSL_VERIFYPEER => false
                ]);
            }

            // make the request
            if ($method === 'POST') {
                $request->post($requestUrl, json_encode($body));
            } elseif ($method === 'GET') {
                $request->get($requestUrl, $body);
            }

            //@TODO: throw if status isn't 200

            // retrieve response
            /** @var string $response */
            $response = $request->getBody();

            $debug['status'] = $request->getStatus();

            // converts response into an array
            $response = json_decode($response, true);

            // fill in current response object for debug
            /** @var array $response */
            $debug['response'] = is_array($response) ? $this->mask($response) : $response;

        } catch (ConfigurationMismatchException $e) {
            // TODO: do something specific related to the module's configuration
            $this->logger->error($e->getMessage(), [
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);

        } catch (Exception $e) {
            // checking out we didn't die for natural reasons
            $status = $request->getStatus();
            $message = $e->getMessage();

            $debug['http_error'] = [
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ];

            $this->logger->error($e->getMessage(), $debug);

            throw $e;
        }

        // The request/response pair is enough to recreate the gateway contract in a mock.
        if ($this->verifiedPurchaseConfiguration->isDebugEnabled()) {
            $this->logger->debug('Connector Debug response', $debug);
        }

        return $response;
    }

    /**
     * Replaces sensitive verification data before a payload reaches the debug log.
     *
     * @param array $payload
     *
     * @return array
     */
    private function mask(array $payload): array
    {
        $sensitiveFields = [
            AttributeInterface::FIELD_CODE,
            AttributeInterface::FIELD_IP_ADDRESS
        ];

        foreach ($payload as $key => $value) {
            if (is_array($value)) {
                $payload[$key] = $this->mask($value);
                continue;
            }

            if (in_array($key, $sensitiveFields, true) && $value !== null && $value !== '') {
                $payload[$key] = '***';
            }
        }

        return $payload;
    }

    /**
     * Executes quick validation against current connector properties
     *
     * @throws ConfigurationMismatchException
     *
     * @return bool
     */
    protected function validate(): bool
    {
        if (!$this->getAuthorizationKey() || $this->getAuthorizationKey() === '') {
            throw new ConfigurationMismatchException(__('No api key provided'));
        }
        if (!$this->getBaseUrl() || $this->getBaseUrl() === '') {
            throw new ConfigurationMismatchException(__('No base url provided'));
        }

        // TODO: complete all required validation besides api key, if needed

        return true;
    }
}
