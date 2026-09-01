<?php
/**
 * Copyright © 2025 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Test\Unit\Model\Promotions;

use Line\Payment\Api\GetPromotionsActionInterface;
use Line\Payment\Api\GetPromotionsByBinActionInterface;
use Line\Payment\Model\Promotions\Exception\PromotionsUnavailable;
use Line\Payment\Model\Promotions\InstallmentPlan;
use Line\Payment\Model\Promotions\InstallmentPlanFactory;
use Line\Payment\Model\Promotions\ResolveInstallmentPlanAction;
use Magento\Framework\Exception\LocalizedException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ResolveInstallmentPlanActionTest extends TestCase
{
    private const BIN = '450799';
    private const BRAND = 'VISA';
    private const MERCHANT = '88884444';
    private const DEFAULT_MERCHANT = '11112222';

    /**
     * @var GetPromotionsByBinActionInterface|MockObject
     */
    private $promotionsByBin;

    /**
     * @var GetPromotionsActionInterface|MockObject
     */
    private $promotions;

    private ResolveInstallmentPlanAction $action;

    protected function setUp(): void
    {
        $this->promotionsByBin = $this->createMock(GetPromotionsByBinActionInterface::class);
        $this->promotions = $this->createMock(GetPromotionsActionInterface::class);

        $planFactory = $this->getMockBuilder(InstallmentPlanFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $planFactory->method('create')->willReturnCallback(
            static function (array $arguments) {
                return new InstallmentPlan(
                    $arguments['installments'],
                    $arguments['rate'],
                    $arguments['merchantNumber']
                );
            }
        );

        $this->action = new ResolveInstallmentPlanAction(
            $this->promotionsByBin,
            $this->promotions,
            $planFactory,
            $this->createMock(LoggerInterface::class)
        );
    }

    public function testResolvesAPlanMatchedByBin(): void
    {
        $this->promotionsByBin->method('get')->willReturn($this->payloadWithInstallments([
            ['quantity' => 3, 'rate' => 1.15],
            ['quantity' => 6, 'rate' => 1.30]
        ]));

        $plan = $this->action->resolve(self::BIN, self::BRAND, 6);

        $this->assertSame(6, $plan->getInstallments());
        $this->assertSame(1.30, $plan->getRate());
        $this->assertSame(self::MERCHANT, $plan->getMerchantNumber());
    }

    public function testFallsBackToBrandWidePromotionsWhenTheBinHasNone(): void
    {
        $this->promotionsByBin->method('get')->willReturn($this->emptyPayload());
        $this->promotions->expects($this->once())
            ->method('get')
            ->with(self::BRAND)
            ->willReturn($this->payloadWithInstallments([['quantity' => 12, 'rate' => 1.5]]));

        $plan = $this->action->resolve(self::BIN, self::BRAND, 12);

        $this->assertSame(1.5, $plan->getRate());
    }

    public function testFallsBackToTheDefaultPlanWhenNoPromotionsExist(): void
    {
        $this->promotionsByBin->method('get')->willReturn($this->emptyPayload());
        $this->promotions->method('get')->willReturn($this->emptyPayload());

        $plan = $this->action->resolve(self::BIN, self::BRAND, 1);

        $this->assertSame(1, $plan->getInstallments());
        $this->assertSame(1.0, $plan->getRate());
        $this->assertSame(self::DEFAULT_MERCHANT, $plan->getMerchantNumber());
    }

    public function testRejectsAnInstallmentCountThatIsNotOffered(): void
    {
        $this->promotionsByBin->method('get')->willReturn($this->payloadWithInstallments([
            ['quantity' => 3, 'rate' => 1.15]
        ]));

        $this->expectException(LocalizedException::class);

        $this->action->resolve(self::BIN, self::BRAND, 6);
    }

    /**
     * @dataProvider unacceptableRateProvider
     *
     * @param mixed $rate
     */
    public function testRejectsARateOutsideTheAcceptedRange($rate): void
    {
        $this->promotionsByBin->method('get')->willReturn($this->payloadWithInstallments([
            ['quantity' => 3, 'rate' => $rate]
        ]));

        $this->expectException(LocalizedException::class);

        $this->action->resolve(self::BIN, self::BRAND, 3);
    }

    /**
     * @return array
     */
    public function unacceptableRateProvider(): array
    {
        return [
            'above the ceiling' => [7.5],
            'infinite' => [INF]
        ];
    }

    /**
     * A rate below 1.0 mirrors what the checkout already rendered with no surcharge
     * (view/frontend/web/js/model/promotions.js:70 does `parseFloat(data.rate) || 1.0`), so it is
     * coerced up to 1.0 instead of being rejected.
     *
     * @dataProvider belowRangeRateProvider
     *
     * @param mixed $rate
     */
    public function testCoercesABelowRangeRateUpToNoSurcharge($rate): void
    {
        $this->promotionsByBin->method('get')->willReturn($this->payloadWithInstallments([
            ['quantity' => 3, 'rate' => $rate]
        ]));

        $plan = $this->action->resolve(self::BIN, self::BRAND, 3);

        $this->assertSame(1.0, $plan->getRate());
    }

    /**
     * @return array
     */
    public function belowRangeRateProvider(): array
    {
        return [
            'below one' => [0.01],
            'zero' => [0],
            'negative' => [-2.0],
            'not a number' => ['abc']
        ];
    }

    public function testRejectsAMismatchedMerchantNumber(): void
    {
        $this->promotionsByBin->method('get')->willReturn($this->payloadWithInstallments([
            ['quantity' => 3, 'rate' => 1.15]
        ]));

        $this->expectException(LocalizedException::class);

        $this->action->resolve(self::BIN, self::BRAND, 3, '99999999');
    }

    public function testPropagatesAnUnavailablePromotionsService(): void
    {
        $this->promotionsByBin->method('get')->willThrowException(new PromotionsUnavailable(__('down')));

        $this->expectException(PromotionsUnavailable::class);

        $this->action->resolve(self::BIN, self::BRAND, 1);
    }

    /**
     * An unreadable payload is not the same as "this card has no promotions".
     */
    public function testFailsClosedOnAnUnreadablePayload(): void
    {
        $this->promotionsByBin->method('get')->willReturn([]);

        $this->expectException(PromotionsUnavailable::class);

        $this->action->resolve(self::BIN, self::BRAND, 1);
    }

    /**
     * @param array $installments
     *
     * @return array
     */
    private function payloadWithInstallments(array $installments): array
    {
        return [
            'promotions' => [
                [
                    'bin' => ['number' => self::BIN, 'cardType' => 'CREDITO'],
                    'merchant' => ['number' => self::MERCHANT],
                    'installments' => $installments
                ]
            ],
            'cardBrand' => self::BRAND,
            'defaultMerchant' => ['number' => self::DEFAULT_MERCHANT]
        ];
    }

    /**
     * @return array
     */
    private function emptyPayload(): array
    {
        return [
            'promotions' => [],
            'cardBrand' => self::BRAND,
            'defaultMerchant' => ['number' => self::DEFAULT_MERCHANT]
        ];
    }
}
