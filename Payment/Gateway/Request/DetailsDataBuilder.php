<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Gateway\Request;

use Line\Payment\Api\Data\Checkout\PaymentAttributeInterface;
use Line\Payment\Api\Data\ConfigInterface;
use Line\Payment\Api\Request\BuilderInterface;
use Line\Payment\Gateway\DataReader;
use Line\Payment\Model\Config;
use Line\Payment\Model\GetTransactionIdentifierAction;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;

class DetailsDataBuilder implements BuilderInterface
{
    private DataReader $reader;
    private GetTransactionIdentifierAction $identifier;

    /**
     * @param DataReader $reader
     * @param GetTransactionIdentifierAction $identifier
     * @param Config $configuration
     */
    public function __construct(
        DataReader $reader,
        GetTransactionIdentifierAction $action
    ) {
        $this->reader = $reader;
        $this->identifier = $action;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject): array
    {
        /** @var PaymentDataObjectInterface $payment */
        $payment = $this->reader->readPayment($buildSubject);

        /** @TODO: improve validation since Braintree made a _lovely_ overwrite of OrderAdapter class */
        /** @var OrderAdapterInterface $order */
        $order = $payment->getOrder();

        // Customer Identifier for this transaction
        // (based on Customer and Order information)
        $identifier = $this->identifier->generate($order);

        // Reference value
        $incrementId = $order->getOrderIncrementId();

        // Amount of installments
        $installments = (int) $payment->getPayment()
            ->getAdditionalInformation(PaymentAttributeInterface::PAYMENT_INSTALLMENTS);

        // Business Number coming from external Promotion information
        $businessNumber = $payment->getPayment()
            ->getAdditionalInformation(PaymentAttributeInterface::PAYMENT_MERCHANT_NUMBER);

        // Rate coefficient from selected installment (e.g. 1.15 = 15% surcharge)
        // Defaults to 1.0 (no surcharge) if not present
        $rate = (float) ($payment->getPayment()
            ->getAdditionalInformation(PaymentAttributeInterface::PAYMENT_INSTALLMENT_RATE) ?: 1.0);

        // Total amount to charge, including installment surcharge
        $amount = $order->getGrandTotalAmount() * $rate;

        // Details data structure
        $details = [
            self::FIELD_DETAIL_BUSINESS_NUMBER => $businessNumber,
            self::FIELD_DETAIL_AMOUNT => $amount,
            self::FIELD_DETAIL_INSTALLMENTS => $installments,
            self::FIELD_CUSTOMER_IDENTIFIER => $identifier,
            self::FIELD_REFERENCE => $incrementId
        ];

        return [
            self::FIELD_DETAIL => [$details]
        ];
    }
}
