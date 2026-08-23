<?php
/**
 * Copyright © 2025 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Test\Unit\Gateway\Request;

use Line\Payment\Api\Data\Checkout\PaymentAttributeInterface;
use Line\Payment\Api\Data\Checkout\SensitiveDataInterface;
use Line\Payment\Api\Request\AttributeInterface;
use Line\Payment\Api\ResolveInstallmentPlanActionInterface;
use Line\Payment\Gateway\DataReader;
use Line\Payment\Gateway\Request\DetailsDataBuilder;
use Line\Payment\Model\Checkout\SensitiveDataRegistry;
use Line\Payment\Model\GetTransactionIdentifierAction;
use Line\Payment\Model\Promotions\Exception\PromotionsUnavailable;
use Line\Payment\Model\Promotions\InstallmentPlan;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Model\InfoInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class DetailsDataBuilderTest extends TestCase
{
    private const BIN = '450799';
    private const GRAND_TOTAL = 1000.0;
    private const MERCHANT = '88884444';

    /**
     * @var SensitiveDataRegistry
     */
    private SensitiveDataRegistry $registry;

    /**
     * @var ResolveInstallmentPlanActionInterface|MockObject
     */
    private $resolver;

    /**
     * @var InfoInterface|MockObject
     */
    private $info;

    /**
     * @var array
     */
    private array $additional = [];

    private DetailsDataBuilder $builder;

    protected function setUp(): void
    {
        $this->registry = new SensitiveDataRegistry();
        $this->resolver = $this->createMock(ResolveInstallmentPlanActionInterface::class);

        $this->additional = [
            PaymentAttributeInterface::PAYMENT_INSTALLMENTS => 6,
            PaymentAttributeInterface::PAYMENT_MERCHANT_NUMBER => self::MERCHANT,
            PaymentAttributeInterface::CREDIT_CARD_TYPE => 'VISA',
            // what a tampered checkout would send; it must never be read
            PaymentAttributeInterface::PAYMENT_INSTALLMENT_RATE => 0.01
        ];

        $this->info = $this->createMock(InfoInterface::class);
        $this->info->method('getAdditionalInformation')->willReturnCallback(
            function ($key = null) {
                return $key === null ? $this->additional : ($this->additional[$key] ?? null);
            }
        );
        $this->info->method('setAdditionalInformation')->willReturnCallback(
            function ($key, $value) {
                $this->additional[$key] = $value;

                return $this->info;
            }
        );

        $order = $this->createMock(OrderAdapterInterface::class);
        $order->method('getOrderIncrementId')->willReturn('000000123');
        $order->method('getGrandTotalAmount')->willReturn(self::GRAND_TOTAL);

        $payment = $this->createMock(PaymentDataObjectInterface::class);
        $payment->method('getPayment')->willReturn($this->info);
        $payment->method('getOrder')->willReturn($order);

        $reader = $this->createMock(DataReader::class);
        $reader->method('readPayment')->willReturn($payment);

        $identifier = $this->createMock(GetTransactionIdentifierAction::class);
        $identifier->method('generate')->willReturn('0123456789abcdef0123456789abcdef');

        $this->builder = new DetailsDataBuilder(
            $reader,
            $identifier,
            $this->registry,
            $this->resolver,
            $this->createMock(LoggerInterface::class)
        );
    }

    public function testChargesTheGrandTotalTimesTheResolvedRate(): void
    {
        $this->givenCardInRegistry();
        $this->resolver->method('resolve')->willReturn(new InstallmentPlan(6, 1.25, self::MERCHANT));

        $result = $this->builder->build([]);
        $details = $result[AttributeInterface::FIELD_DETAIL][0];

        $this->assertSame(self::GRAND_TOTAL * 1.25, $details[AttributeInterface::FIELD_DETAIL_AMOUNT]);
        $this->assertSame(6, $details[AttributeInterface::FIELD_DETAIL_INSTALLMENTS]);
        $this->assertSame(self::MERCHANT, $details[AttributeInterface::FIELD_DETAIL_BUSINESS_NUMBER]);
    }

    /**
     * @dataProvider tamperedRateProvider
     *
     * @param mixed $submitted
     */
    public function testIgnoresTheRateSubmittedByTheBrowser($submitted): void
    {
        $this->givenCardInRegistry();
        $this->additional[PaymentAttributeInterface::PAYMENT_INSTALLMENT_RATE] = $submitted;
        $this->resolver->method('resolve')->willReturn(new InstallmentPlan(6, 1.25, self::MERCHANT));

        $details = $this->builder->build([])[AttributeInterface::FIELD_DETAIL][0];

        $this->assertSame(self::GRAND_TOTAL * 1.25, $details[AttributeInterface::FIELD_DETAIL_AMOUNT]);
    }

    /**
     * @return array
     */
    public function tamperedRateProvider(): array
    {
        return [
            'fraction' => [0.01],
            'zero' => [0],
            'negative' => [-1.0],
            'not numeric' => ['free'],
            'absent' => [null]
        ];
    }

    public function testWritesTheResolvedRateBack(): void
    {
        $this->givenCardInRegistry();
        $this->resolver->method('resolve')->willReturn(new InstallmentPlan(6, 1.25, self::MERCHANT));

        $this->builder->build([]);

        $this->assertSame(
            1.25,
            $this->additional[PaymentAttributeInterface::PAYMENT_INSTALLMENT_RATE]
        );
    }

    public function testPassesTheBinFromTheRegistryToTheResolver(): void
    {
        $this->givenCardInRegistry();

        $this->resolver->expects($this->once())
            ->method('resolve')
            ->with(self::BIN, 'VISA', 6, self::MERCHANT)
            ->willReturn(new InstallmentPlan(6, 1.0, self::MERCHANT));

        $this->builder->build([]);
    }

    public function testFailsHardWhenTheRegistryIsEmpty(): void
    {
        $this->expectException(LocalizedException::class);

        $this->builder->build([]);
    }

    public function testPropagatesAnUnavailablePromotionsService(): void
    {
        $this->givenCardInRegistry();
        $this->resolver->method('resolve')->willThrowException(new PromotionsUnavailable(__('down')));

        $this->expectException(PromotionsUnavailable::class);

        $this->builder->build([]);
    }

    public function testPropagatesARejectedSelection(): void
    {
        $this->givenCardInRegistry();
        $this->resolver->method('resolve')->willThrowException(new LocalizedException(__('nope')));

        $this->expectException(LocalizedException::class);

        $this->builder->build([]);
    }

    /**
     * @return void
     */
    private function givenCardInRegistry(): void
    {
        $card = $this->createMock(SensitiveDataInterface::class);
        $card->method('getBin')->willReturn(self::BIN);
        $card->method('getPan')->willReturn('4507990000001026');
        $card->method('getCvv')->willReturn('123');
        $card->method('getFingerprint')->willReturn(str_repeat('a', 64));

        $this->registry->set($card);
    }
}
