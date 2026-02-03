<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Gateway\Request;

use Line\Payment\Api\Request\BuilderInterface;
use PayPal\Braintree\Gateway\Data\Order\OrderAdapter;
use Line\Payment\Gateway\DataReader;

/**
 *
 */
class CustomerInformationBuilder implements BuilderInterface
{
    private DataReader $reader;

    /**
     * @param DataReader $reader
     */
    public function __construct(DataReader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject): array
    {
        $payment = $this->reader->readPayment($buildSubject);

        /** @TODO: improve validation since there could be an scenario where Braintree is disabled */
        /** @var OrderAdapter $order */
        $order = $payment->getOrder();

        $billing = $order->getBillingAddress();

        // Buyer Fullname
        $fullname = $billing->getFirstname() . ' ' . $billing->getLastname();

        // Buyer email address
        $email = $billing->getEmail();

        // Reference value
        $incrementId = $order->getOrderIncrementId();

        // Customer IP Address
        $ipAddress = $order->getRemoteIp();

        return [
            self::FIELD_CARDHOLDER_FULLNAME => $fullname,
            self::FIELD_CARDHOLDER_EMAIL => $email,
            self::FIELD_REFERENCE => $incrementId,
            self::FIELD_CUSTOMER_IP => $ipAddress
        ];
    }
}
