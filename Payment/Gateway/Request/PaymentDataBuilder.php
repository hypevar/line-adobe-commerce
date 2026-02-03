<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Gateway\Request;

use Line\Payment\Api\Data\Checkout\PaymentAdditionalAttributeInterface;
use Line\Payment\Api\Data\Checkout\PaymentAttributeInterface;
use Line\Payment\Api\Request\BuilderInterface;
use Line\Payment\Gateway\DataReader;
use Line\Payment\Gateway\DataResolver\CardTypeResolver;
use Line\Payment\Gateway\DataResolver\EmitterCodeResolver;
use Line\Payment\Gateway\DataResolver\ExpirationDateResolver;
use Magento\Payment\Model\InfoInterface;

/**
 * Builds Payment information into the request
 */
class PaymentDataBuilder implements BuilderInterface
{
    private DataReader $reader;
    private ExpirationDateResolver $expiration;
    private EmitterCodeResolver $emitter;
    private CardTypeResolver $cardType;

    /**
     * @param DataReader $reader
     */
    public function __construct(
        DataReader $reader,
        ExpirationDateResolver $expirationResolver,
        EmitterCodeResolver $emitterResolver,
        CardTypeResolver $typeResolver
    ) {
        $this->reader = $reader;
        $this->expiration = $expirationResolver;
        $this->emitter = $emitterResolver;
        $this->cardType = $typeResolver;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject): array
    {
        $result = [];
        $payment = $this->reader->readPayment($buildSubject);

        /** @var array $additional */
        $additional = $payment->getPayment()->getAdditionalInformation();

        // Checkout Card (internal code)
        $cardBaseType = $additional[PaymentAttributeInterface::CREDIT_CARD_TYPE];

        // Emitter Code
        $creditCardEmitterCode = $this->emitter->get($cardBaseType);

        // Credit Card Type
        $creditCardType = $additional[PaymentAttributeInterface::CREDIT_CARD_METHOD];
        // $creditCardType = $this->cardType->get($creditCardEmitterCode);

        // Credit Card Number
        $creditCardNumber = $additional[PaymentAttributeInterface::CREDIT_CARD_NUMBER];

        // Expiration Date
        $creditCardExpirationDate =  $this->expiration->get(
            $additional[PaymentAttributeInterface::CREDIT_CARD_EXP_YEAR],
            $additional[PaymentAttributeInterface::CREDIT_CARD_EXP_MONTH]
        );

        // CVV
        $creditCardCVV = $additional[PaymentAttributeInterface::CREDIT_CARD_CVV];

        // Buyer's document type
        $holderDocumentType = $additional[PaymentAttributeInterface::CREDIT_CARD_DOC_TYPE];
        // Buyer's document number
        $holderDocumentNumber = $additional[PaymentAttributeInterface::CREDIT_CARD_DOC_NUMBER];

        $result = [
            self::FIELD_CREDIT_CARD_NUMBER => $creditCardNumber,
            self::FIELD_CREDIT_CARD_EXPIRATION_DATE => $creditCardExpirationDate,
            self::FIELD_CREDIT_CARD_CVV => $creditCardCVV,
            self::FIELD_CREDIT_CARD_TYPE => $creditCardType,
            self::FIELD_CARDHOLDER_DOCUMENT_TYPE => $holderDocumentType,
            self::FIELD_CARDHOLDER_DOCUMENT_NUMBER => $holderDocumentNumber,
            self::FIELD_CREDIT_CARD_EMITTER_CODE => $creditCardEmitterCode
        ];

        return $result;
    }
}
