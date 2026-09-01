<?php
/**
 * Copyright © 2025 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Test\Unit\Model\Client;

use Line\Payment\Api\Data\ConfigInterface;
use Line\Payment\Api\Response\AttributeInterface;
use Line\Payment\Gateway\Api\ResponseFactory;
use Line\Payment\Model\Client\Connector;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\HTTP\Client\CurlFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * The gateway answers an authorization with a JSON object carrying an `identifier`.
 *
 * Anything else — an HTML error page, an empty body, a misconfigured module — must still come back
 * as an array the gateway validator can reject, never as `null`: `post()` is typed `: array`, so a
 * `null` there is a TypeError in the middle of taking a payment.
 */
class ConnectorTest extends TestCase
{
    private const BASE_URL = 'https://gateway.test/api/';

    /**
     * @var Curl|MockObject
     */
    private $curl;

    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;

    /**
     * @var CurlFactory|MockObject
     */
    private $curlFactory;

    protected function setUp(): void
    {
        $this->curl = $this->createMock(Curl::class);

        $this->curlFactory = $this->getMockBuilder(CurlFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->curlFactory->method('create')->willReturn($this->curl);

        $this->logger = $this->createMock(LoggerInterface::class);
    }

    public function testReturnsTheDecodedBodyOnSuccess(): void
    {
        $this->answer(200, '{"identifier":"tx-1","status":"APPROVED"}');

        $response = $this->connector()->post('payments', ['amount' => 100]);

        $this->assertSame(['identifier' => 'tx-1', 'status' => 'APPROVED'], $response);
    }

    public function testKeepsTheGatewayOwnErrorPayloadWhenTheStatusIsNotSuccessful(): void
    {
        $this->answer(400, '{"message":"Insufficient funds","error_code":51}');

        $response = $this->connector()->post('payments', ['amount' => 100]);

        $this->assertSame('Insufficient funds', $response[AttributeInterface::FIELD_MESSAGE]);
        $this->assertSame(51, $response[AttributeInterface::FIELD_ERROR_CODE]);
    }

    public function testReturnsAnErrorEnvelopeWhenTheBodyIsNotJson(): void
    {
        $this->answer(502, '<html><body>Bad Gateway</body></html>');

        $this->assertIsRejectableEnvelope($this->connector()->post('payments', ['amount' => 100]));
    }

    public function testReturnsAnErrorEnvelopeWhenTheBodyIsEmpty(): void
    {
        $this->answer(200, '');

        $this->assertIsRejectableEnvelope($this->connector()->post('payments', ['amount' => 100]));
    }

    public function testReturnsAnErrorEnvelopeWhenTheModuleIsNotConfigured(): void
    {
        $connector = $this->connector();
        $connector->setAuthorizationKey('');

        $this->assertIsRejectableEnvelope($connector->post('payments', ['amount' => 100]));
    }

    public function testReturnsAnErrorEnvelopeWhenTheRequestBlowsUp(): void
    {
        $this->curl->method('getStatus')->willReturn(0);
        $this->curl->method('post')->willThrowException(new \RuntimeException('cURL timeout'));

        $this->assertIsRejectableEnvelope($this->connector()->post('payments', ['amount' => 100]));
    }

    public function testGetIsGuardedTheSameWayAsPost(): void
    {
        $this->curl->method('getStatus')->willReturn(500);
        $this->curl->method('getBody')->willReturn('');

        $this->assertIsRejectableEnvelope($this->connector()->get('payments/tx-1', []));
    }

    /**
     * The gateway validator rejects a payload with no `identifier`, and surfaces `message` to the
     * customer when it is there.
     *
     * @param mixed $response
     *
     * @return void
     */
    private function assertIsRejectableEnvelope($response): void
    {
        $this->assertIsArray($response);
        $this->assertArrayNotHasKey(AttributeInterface::FIELD_IDENTIFIER, $response);
        $this->assertArrayHasKey(AttributeInterface::FIELD_MESSAGE, $response);
        $this->assertNotSame('', (string) $response[AttributeInterface::FIELD_MESSAGE]);
    }

    /**
     * @param int $status
     * @param string $body
     *
     * @return void
     */
    private function answer(int $status, string $body): void
    {
        $this->curl->method('getStatus')->willReturn($status);
        $this->curl->method('getBody')->willReturn($body);
    }

    /**
     * @return Connector
     */
    private function connector(): Connector
    {
        $config = $this->createMock(ConfigInterface::class);
        $config->method('getApiSslIsActive')->willReturn(false);

        $connector = new Connector(
            $config,
            $this->curlFactory,
            $this->getMockBuilder(ResponseFactory::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['create'])
                ->getMock(),
            $this->createMock(DataObjectHelper::class),
            $this->logger
        );

        $connector->setBaseUrl(self::BASE_URL)
            ->setAuthorizationKey('an-api-key')
            ->setUserAgent('Magento Line Payment Gateway')
            ->setApiVersion('1.0');

        return $connector;
    }
}
