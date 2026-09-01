<?php
/**
 * Copyright © 2025 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Test\Unit\Model\Promotions;

use Line\Payment\Model\Promotions\DataConverter;
use Line\Payment\Model\Promotions\Exception\PromotionsUnavailable;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class DataConverterTest extends TestCase
{
    /**
     * A Tuesday, so `daysOfWeek` fixtures only have to name one day.
     */
    private const NOW = '2026-08-25 12:00:00';

    private DataConverter $converter;

    protected function setUp(): void
    {
        $timezone = $this->createMock(TimezoneInterface::class);
        $timezone->method('date')->willReturnCallback(
            static function () {
                return new \DateTime(self::NOW);
            }
        );

        $this->converter = new DataConverter($timezone, $this->createMock(LoggerInterface::class));
    }

    public function testThrowsWhenThePayloadCarriesNoBrandsKey(): void
    {
        $this->expectException(PromotionsUnavailable::class);

        $this->converter->convert(['errors' => true, 'message' => 'No promotions found', 'result' => []]);
    }

    public function testThrowsWhenBrandsIsNotAnArray(): void
    {
        $this->expectException(PromotionsUnavailable::class);

        $this->converter->convert(['brands' => 'service unavailable']);
    }

    public function testReturnsAnEmptyPlanWhenTheCardHasNoBrandAtAll(): void
    {
        $result = $this->converter->convert(['brands' => []]);

        $this->assertSame(
            [
                'promotions' => [],
                'cardBrand' => '',
                'defaultMerchant' => false
            ],
            $result
        );
    }

    public function testToleratesABrandThatCarriesNoOptions(): void
    {
        $result = $this->converter->convert(['brands' => [['cardBrand' => 'VISA']]]);

        $this->assertSame([], $result['promotions']);
        $this->assertSame('VISA', $result['cardBrand']);
    }

    public function testMapsAnAvailablePromotion(): void
    {
        $result = $this->converter->convert(['brands' => [$this->brand()]]);

        $this->assertSame('VISA', $result['cardBrand']);
        $this->assertSame(['number' => '11112222'], $result['defaultMerchant']);
        $this->assertCount(1, $result['promotions']);

        $promotion = $result['promotions'][0];

        $this->assertSame(['number' => '450799', 'cardType' => 'CREDIT'], $promotion['bin']);
        $this->assertSame(['number' => '88884444'], $promotion['merchant']);
        $this->assertSame(
            [['quantity' => 3, 'rate' => 1.15, 'name' => '3 CUOTAS', 'displayName' => '3 cuotas']],
            $promotion['installments']
        );
    }

    public function testDropsTheDebitOptionFromTheInstallments(): void
    {
        $brand = $this->brand();
        $brand['options'][0]['installments'][] = [
            'id' => 9,
            'quantity' => 1,
            'rate' => 1.0,
            'name' => 'DEBITO',
            'displayName' => 'DEBITO'
        ];

        $result = $this->converter->convert(['brands' => [$brand]]);

        $this->assertCount(1, $result['promotions'][0]['installments']);
        $this->assertSame(3, $result['promotions'][0]['installments'][0]['quantity']);
    }

    public function testSkipsAPromotionThatIsNotEnabled(): void
    {
        $brand = $this->brand();
        $brand['options'][0]['enabled'] = false;

        $result = $this->converter->convert(['brands' => [$brand]]);

        $this->assertSame([], $result['promotions']);
        $this->assertSame('VISA', $result['cardBrand']);
    }

    public function testSkipsAPromotionThatDoesNotRunToday(): void
    {
        $brand = $this->brand();
        $brand['options'][0]['daysOfWeek'] = ['SUNDAY'];

        $result = $this->converter->convert(['brands' => [$brand]]);

        $this->assertSame([], $result['promotions']);
    }

    /**
     * @return array
     */
    private function brand(): array
    {
        return [
            'cardBrand' => 'VISA',
            'defaultMerchant' => ['number' => '11112222', 'activationKey' => 'secret'],
            'options' => [
                [
                    'enabled' => true,
                    'daysOfWeek' => ['MONDAY', 'TUESDAY', 'WEDNESDAY'],
                    'bank' => ['bines' => [['number' => '450799', 'cardType' => 'CREDIT']]],
                    'merchant' => ['number' => '88884444'],
                    'installments' => [
                        [
                            'id' => 7,
                            'quantity' => 3,
                            'rate' => 1.15,
                            'name' => '3 CUOTAS',
                            'displayName' => '3 cuotas'
                        ]
                    ]
                ]
            ]
        ];
    }
}
