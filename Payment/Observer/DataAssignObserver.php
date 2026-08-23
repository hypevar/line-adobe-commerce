<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Observer;

use Line\Payment\Api\Data\Checkout\PaymentAttributeInterface;
use Line\Payment\Model\Checkout\SensitiveDataFactory;
use Line\Payment\Model\Checkout\SensitiveDataRegistry;
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
     * Fields copied into additional_information, which is persisted on the quote.
     *
     * CREDIT_CARD_NUMBER and CREDIT_CARD_CVV are deliberately absent: they go to the
     * request-scoped registry instead and never reach storage.
     * PAYMENT_INSTALLMENT_RATE is deliberately absent: the rate is resolved server side at
     * authorization time and must not be taken from the browser.
     *
     * @var string[]
     */
    protected $additionalFields = [
        self::CREDIT_CARD_HOLDER_NAME,
        self::CREDIT_CARD_DOC_NUMBER,
        self::CREDIT_CARD_DOC_TYPE,
        self::CREDIT_CARD_TYPE,
        self::CREDIT_CARD_METHOD,
        self::CREDIT_CARD_EXP_YEAR,
        self::CREDIT_CARD_EXP_MONTH,
        self::PAYMENT_INSTALLMENTS,
        self::PAYMENT_MERCHANT_NUMBER
    ];

    private SensitiveDataRegistry $registry;
    private SensitiveDataFactory $sensitiveDataFactory;

    /**
     * @param SensitiveDataRegistry $registry
     * @param SensitiveDataFactory $sensitiveDataFactory
     */
    public function __construct(
        SensitiveDataRegistry $registry,
        SensitiveDataFactory $sensitiveDataFactory
    ) {
        $this->registry = $registry;
        $this->sensitiveDataFactory = $sensitiveDataFactory;
    }

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

        $this->captureSensitiveData($additionalData);
    }

    /**
     * Hold the card data in memory for the request builders. Never on $info.
     *
     * @param array $additionalData
     * @return void
     */
    private function captureSensitiveData(array $additionalData): void
    {
        $pan = $additionalData[self::CREDIT_CARD_NUMBER] ?? null;
        $cvv = $additionalData[self::CREDIT_CARD_CVV] ?? null;

        if (!is_scalar($pan) || (string) $pan === '') {
            return;
        }

        if (!is_scalar($cvv)) {
            $cvv = '';
        }

        $this->registry->set(
            $this->sensitiveDataFactory->create([
                'pan' => (string) $pan,
                'cvv' => (string) $cvv
            ])
        );
    }
}
