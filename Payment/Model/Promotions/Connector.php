<?php
/**
 * Copyright © 2024 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Model\Promotions;

use Line\Payment\Api\Data\ConfigInterface;
use Line\Payment\Gateway\Api\ResponseFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\HTTP\Client\CurlFactory;
use Psr\Log\LoggerInterface;

/**
 * Main entrypoint for connections against the service
 */
class Connector
{
    /**#@+
     * @access private
     * @var string
     */
    /**
     * Header's auth key to be included in every request
     */
    private string $authorization;
    /**
     * Api base url which includes: protocol, domain and api_version
     */
    private string $base_url;
    /**
     * Header's Api Version value
     */
    private string $api_version;
    /**
     * Header's User Agent value
     */
    private string $user_agent;
    /**#@-*/

    protected CurlFactory $curlFactory;
    protected ConfigInterface $configuration;
    protected LoggerInterface $logger;
    protected ResponseFactory $response;
    protected DataObjectHelper $objectHelper;

    public function __construct(
        ConfigInterface $config,
        CurlFactory $curl,
        ResponseFactory $response,
        DataObjectHelper $objectHelper,
        LoggerInterface $logger
    ) {
        $this->configuration = $config;
        $this->curlFactory = $curl;
        $this->response = $response;
        $this->objectHelper = $objectHelper;
        $this->logger = $logger;
    }

    /**
     * @return string
     */
    private function getAuthorizationKey(): string
    {
        return $this->authorization;
    }

    /**
     * @inheritDoc
     */
    public function setAuthorizationKey(string $value): self
    {
        $this->authorization = $value;
        return $this;
    }

    /**
     * @return string
     */
    private function getUserAgent(): string
    {
        return $this->user_agent;
    }

    /**
     * @inheritDoc
     */
    public function setUserAgent(string $value): self
    {
        $this->user_agent = $value;
        return $this;
    }

    /**
     * @return string
     */
    private function getApiVersion(): string
    {
        return $this->api_version;
    }

    /**
     * @inheritDoc
     */
    public function setApiVersion(string $value): self
    {
        $this->api_version = $value;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setBaseUrl(string $value): self
    {
        $this->base_url = $value;
        return $this;
    }

    /**
     * @return string
     */
    private function getBaseUrl(): string
    {
        return $this->base_url;
    }

    /**
     * @return array
     */
    public function get($path, $body): array
    {
        return $this->makeRequest('GET', $path, $body);
    }

    /**
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

            $this->logger->debug('Promotions Debug request', [
                'method' => $method,
                'http-uri' => $httpUri,
                'body' => $body
            ]);

            $requestUrl = $this->getBaseUrl() . $httpUri;

            /** @var Curl $request */
            $request = $this->curlFactory->create();

            // debug: method and url values
            $debug['method'] = $method;
            $debug['url'] = $requestUrl;

            // basic setup
            $authorization = 'ApiKey' . ' ' . $this->getAuthorizationKey();
            $request->addHeader('Authorization', $authorization);
            $request->addHeader('Accept', 'application/json');
            $request->addHeader('Content-Type', 'application/json');
            $request->addHeader('User-Agent', $this->getUserAgent());
            $request->addHeader('X-ApiVersion', $this->getApiVersion());

            // Headers setup
            $request->setTimeout(30);

            // SSL configuration
            if ($this->configuration->getApiSslIsActive()) {
                $request->setOptions([
                    CURLOPT_SSLVERSION => $this->configuration->getApiSslVersion(),
                    CURLOPT_SSL_VERIFYPEER => true,
                    CURLOPT_SSL_VERIFYHOST => 2
                ]);
            }

            // make the request
            if ($method === 'POST') {
                $request->post($requestUrl, json_encode($body));
            } elseif ($method === 'GET') {
                $request->get($requestUrl, $body);
            }

            // retrieve response
            /** @var string $response */
            $response = $request->getBody();

            // converts response into an array
            $response = json_decode($response, true);

            // fill in current response object for debug
            /** @var array $response */
            $debug['response'] = $response;

        } catch (ConfigurationMismatchException $e) {
            // TODO: do something specific related to the module's configuration
            $this->logger->error($e->getMessage(), [
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);

        } catch (\Exception $e) {
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

        // final success request data debug information
        // forcing to encode it as a JSON to avoid `9-level deep` clipping
        $this->logger->debug('Promotions Debug response', [json_encode($debug)]);

        return $response;
    }

    /**
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
