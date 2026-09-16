<?php
/**
 * Copyright © 2025 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Test\Unit\Model\Promotions;

use Line\Payment\Api\Data\ConfigInterface;
use Line\Payment\Model\Promotions\Adapter;
use Line\Payment\Model\Promotions\Connector;
use Line\Payment\Model\Promotions\DataConverter;
use PHPUnit\Framework\TestCase;

/**
 * The promotions service answers with the terminal `activationKey` inside every merchant object.
 * That key is a credential: it must never leave the adapter, no matter which consumer reads the
 * payload (BIN lookup, brand lookup, raw cache) or how deep the merchant object is nested.
 */
class AdapterTest extends TestCase
{
    private Adapter $adapter;

    /**
     * @var Connector|\PHPUnit\Framework\MockObject\MockObject
     */
    private $connector;

    protected function setUp(): void
    {
        $config = $this->createMock(ConfigInterface::class);
        $config->method('getPromotionsCredentials')->willReturn(['marketplace', 'account']);

        $this->connector = $this->createMock(Connector::class);
        foreach (['setBaseUrl', 'setAuthorizationKey', 'setUserAgent', 'setApiVersion'] as $setter) {
            $this->connector->method($setter)->willReturnSelf();
        }

        $this->adapter = new Adapter($config, $this->connector, $this->createMock(DataConverter::class));
    }

    public function testStripsEveryActivationKeyFromTheRawPayload(): void
    {
        $this->connector->method('get')->willReturn([
            'brands' => [
                [
                    'cardBrand' => 'VISA',
                    'defaultMerchant' => ['number' => '11112222', 'activationKey' => 'secret-1'],
                    'options' => [
                        ['merchant' => ['number' => '88884444', 'activationKey' => 'secret-2']],
                        ['merchant' => null],
                    ]
                ]
            ]
        ]);

        $result = $this->adapter->get('/promotions/%s/%s');

        $this->assertStringNotContainsString('activationKey', json_encode($result));
        $this->assertSame('11112222', $result['brands'][0]['defaultMerchant']['number']);
        $this->assertSame('88884444', $result['brands'][0]['options'][0]['merchant']['number']);
        $this->assertNull($result['brands'][0]['options'][1]['merchant']);
    }
}
