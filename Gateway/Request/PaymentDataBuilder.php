<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Gateway\Request;

use Line\Payment\Api\Data\Checkout\PaymentAttributeInterface;
use Line\Payment\Api\Request\BuilderInterface;
use Line\Payment\Gateway\DataReader;
use Line\Payment\Gateway\DataResolver\CardTypeResolver;
use Line\Payment\Gateway\DataResolver\EmitterCodeResolver;
use Line\Payment\Gateway\DataResolver\ExpirationDateResolver;
use Line\Payment\Model\Checkout\SensitiveDataRegistry;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

/**
 * Builds Payment information into the request
 */
class PaymentDataBuilder implements BuilderInterface
{
    private DataReader $reader;
    private ExpirationDateResolver $expiration;
    private EmitterCodeResolver $emitter;
    private CardTypeResolver $cardType;
    private SensitiveDataRegistry $registry;
    private LoggerInterface $logger;

    /**
     * @param DataReader $reader
     * @param ExpirationDateResolver $expirationResolver
     * @param EmitterCodeResolver $emitterResolver
     * @param CardTypeResolver $typeResolver
     * @param SensitiveDataRegistry $registry
     * @param LoggerInterface $logger
     */
    public function __construct(
        DataReader $reader,
        ExpirationDateResolver $expirationResolver,
        EmitterCodeResolver $emitterResolver,
        CardTypeResolver $typeResolver,
        SensitiveDataRegistry $registry,
        LoggerInterface $logger
    ) {
        $this->reader = $reader;
        $this->expiration = $expirationResolver;
        $this->emitter = $emitterResolver;
        $this->cardType = $typeResolver;
        $this->registry = $registry;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     * @throws LocalizedException
     */
    public function build(array $buildSubject): array
    {
        $payment = $this->reader->readPayment($buildSubject);

        /** @var array $additional */
        $additional = $payment->getPayment()->getAdditionalInformation();

        // Checkout Card (internal code)
        $cardBaseType = $additional[PaymentAttributeInterface::CREDIT_CARD_TYPE];

        // Emitter Code
        $creditCardEmitterCode = $this->emitter->get($cardBaseType);

        // Credit Card Type
        $creditCardType = $additional[PaymentAttributeInterface::CREDIT_CARD_METHOD];

        $card = $this->registry->get();

        if ($card === null) {
            $this->logger->error(
                'Line Payment: no card data in the request scope at authorization time. '
                . 'The order was placed on a request that did not carry the checkout payment data.',
                ['order_increment_id' => $payment->getOrder()->getOrderIncrementId()]
            );

            throw new LocalizedException(
                __('We can\'t process this payment right now. Please re-enter your card details and try again.')
            );
        }

        // Expiration Date
        $creditCardExpirationDate =  $this->expiration->get(
            $additional[PaymentAttributeInterface::CREDIT_CARD_EXP_YEAR],
            $additional[PaymentAttributeInterface::CREDIT_CARD_EXP_MONTH]
        );

        // Buyer's document type
        $holderDocumentType = $additional[PaymentAttributeInterface::CREDIT_CARD_DOC_TYPE];
        // Buyer's document number
        $holderDocumentNumber = $additional[PaymentAttributeInterface::CREDIT_CARD_DOC_NUMBER];

        return [
            self::FIELD_CREDIT_CARD_NUMBER => $card->getPan(),
            self::FIELD_CREDIT_CARD_EXPIRATION_DATE => $creditCardExpirationDate,
            self::FIELD_CREDIT_CARD_CVV => $card->getCvv(),
            self::FIELD_CREDIT_CARD_TYPE => $creditCardType,
            self::FIELD_CARDHOLDER_DOCUMENT_TYPE => $holderDocumentType,
            self::FIELD_CARDHOLDER_DOCUMENT_NUMBER => $holderDocumentNumber,
            self::FIELD_CREDIT_CARD_EMITTER_CODE => $creditCardEmitterCode
        ];
    }
}
