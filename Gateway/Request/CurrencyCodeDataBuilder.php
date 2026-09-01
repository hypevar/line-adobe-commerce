<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Gateway\Request;

use Line\Payment\Api\Request\BuilderInterface;
use Line\Payment\Gateway\DataReader;
use PayPal\Braintree\Gateway\Data\Order\OrderAdapter;

/**
 *
 */
class CurrencyCodeDataBuilder implements BuilderInterface
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

        return [
            self::FIELD_CURRENCY => $order->getCurrencyCode()
        ];
    }
}
