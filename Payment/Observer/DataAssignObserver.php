<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Observer;

use Line\Payment\Api\Data\Checkout\PaymentAttributeInterface;
use Magento\Framework\Event\Observer;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;

/**
 * Ports Checkout Data into the Payment additional information field to process later
 */
class DataAssignObserver extends AbstractDataAssignObserver
    implements PaymentAttributeInterface
{
    /**
     * @var string[]
     */
    protected $additionalFields = [
        self::CREDIT_CARD_HOLDER_NAME,
        self::CREDIT_CARD_DOC_NUMBER,
        self::CREDIT_CARD_DOC_TYPE,
        self::CREDIT_CARD_NUMBER,
        self::CREDIT_CARD_TYPE,
        self::CREDIT_CARD_METHOD,
        self::CREDIT_CARD_EXP_YEAR,
        self::CREDIT_CARD_EXP_MONTH,
        self::CREDIT_CARD_CVV,
        self::PAYMENT_INSTALLMENTS,
        self::PAYMENT_MERCHANT_NUMBER,
        self::PAYMENT_INSTALLMENT_RATE
    ];

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $data = $this->readDataArgument($observer);

        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);

        // means we're not getting the data from Checkout Form
        if (!is_array($additionalData)) {
            return;
        }

        /** @var InfoInterface $info */
        $info = $this->readPaymentModelArgument($observer);

        foreach ($this->additionalFields as $key) {
            if (isset($additionalData[$key])) {
                $info->setAdditionalInformation(
                    $key,
                    $additionalData[$key]
                );
            }
        }
    }
}
