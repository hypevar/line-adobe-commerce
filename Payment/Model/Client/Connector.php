<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Model\Client;

use Line\Payment\Api\Data\ConfigInterface;
use Line\Payment\Api\Data\ConnectorInterface;
use Line\Payment\Api\Response\AttributeInterface;
use Line\Payment\Gateway\Api\ResponseFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\Phrase;
use Psr\Log\LoggerInterface;

/**
 * Main entrypoint for connections against the service
 */
class Connector implements ConnectorInterface
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
    public function setAuthorizationKey(string $value): ConnectorInterface
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
    public function setUserAgent(string $value): ConnectorInterface
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
     * @return string
     */
    private function getBaseUrl(): string
    {
        return $this->base_url;
    }

    /**
     * @return array
     */
    public function get($path, $body)
    {
        return $this->makeRequest(self::METHOD_GET, $path, $body);
    }

    /**
     * @return array
     */
    public function post($path, $body): array
    {
        return $this->makeRequest(self::METHOD_POST, $path, $body);
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

            $debugBody = $this->mask($body);

            if ($this->configuration->isDebugEnabled()) {
                $this->logger->debug('Connector Debug request', [
                    'method' => $method,
                    'http-uri' => $httpUri,
                    'body' => $debugBody
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
            $status = (int) $request->getStatus();
            $debug['status'] = $status;

            // converts response into an array
            $response = json_decode($response, true);

            if (!is_array($response)) {
                $debug['http_error'] = ['error' => 'the response body could not be read as JSON'];

                $this->logger->error('Connector: unreadable response body', $debug);

                return $this->errorResponse();
            }

            if ($status < 200 || $status >= 300) {
                $this->logger->error('Connector: the service answered with HTTP ' . $status, $debug);
            }

            /** @var array $response */
            $debug['response'] = $this->mask($response);

        } catch (ConfigurationMismatchException $e) {
            // TODO: do something specific related to the module's configuration
            $this->logger->error($e->getMessage(), [
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);

            return $this->errorResponse(__('The payment method is not configured correctly.'));

        } catch (\Exception $e) {
            // checking out we didn't die for natural reasons
            $debug['http_error'] = [
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ];

            if (isset($request)) {
                $debug['status'] = $request->getStatus();
            }

            $this->logger->error($e->getMessage(), $debug);

            return $this->errorResponse();
        }

        // The request/response pair is enough to recreate the gateway contract in a mock.
        if ($this->configuration->isDebugEnabled()) {
            $this->logger->debug('Connector Debug response', $debug);
        }

        return $response;
    }

    /**
     * A response the gateway validator can reject on its own terms: no `identifier`, and a message
     * to put in front of the customer.
     *
     * @see \Line\Payment\Gateway\Validator\ResponseValidator
     *
     * @param Phrase|null $message
     *
     * @return array
     */
    private function errorResponse(?Phrase $message = null): array
    {
        $message = $message ?: __('We could not reach the payment service. Please try again in a few minutes.');

        return [AttributeInterface::FIELD_MESSAGE => (string) $message];
    }

    /**
     * Replaces the cardholder fields of a request or response payload before it is logged.
     *
     * Applied recursively: the gateway repeats the card number inside the `Detalle` node.
     *
     * @param array $payload
     *
     * @return array
     */
    protected function mask(array $payload): array
    {
        $masked = [
            'NumeroTarjeta',
            'NumeroCuenta',
            'Token',
            'CodigoSeguridad',
            'FechaExpiracion',
            'NombreTitular',
            'DocumentoTitular',
            'EmailTitular',
            'TrackI',
            'TrackII',
            'IPAddress'
        ];

        foreach ($payload as $key => $value) {
            if (is_array($value)) {
                $payload[$key] = $this->mask($value);
                continue;
            }

            if (in_array($key, $masked, true) && $value !== null && $value !== '') {
                $payload[$key] = '***';
            }
        }

        return $payload;
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
