<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Gateway\Response;

use Line\Payment\Api\Data\Checkout\PaymentAttributeInterface;
use Line\Payment\Api\ResponseInterface;
use Line\Payment\Gateway\DataReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;

/**
 * Retrieve and persist Credit Card information
 */
class CreditCardHandler implements HandlerInterface
{
    /**
     * @var string
     */
    const NO_CARD_MESSAGE = 'Card not informed';

    /**
     * @var DataReader $reader
     */
    private $reader;

    /**
     * @param DataReader $reader
     */
    public function __construct(
        DataReader $reader
    ) {
        $this->reader = $reader;
    }

    /**
     * @param array $handlingSubject
     * @param array $response
     */
    public function handle(array $handlingSubject, array $response)
    {
        $object = $this->reader->readPayment($handlingSubject);

        /** @var OrderPaymentInterface|InfoInterface $payment */
        $payment = $object->getPayment();

        /** @var ResponseInterface $transaction */
        $transaction = $this->reader->readTransaction($response);

        // If brand isn't coming in the gateway response
        // then try to grab that from the information posted in
        // the Checkout form, which is hold in the Payment `additional_information`

        $ccType = $transaction->getCreditCardBrand();

        if (!$ccType) {
            $ccType = $payment->getAdditionalInformation(
                PaymentAttributeInterface::CREDIT_CARD_TYPE
            ) ?? __(self::NO_CARD_MESSAGE);
        }

        $payment->setCcType($ccType);
    }
}
