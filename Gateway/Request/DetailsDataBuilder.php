<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Gateway\Request;

use Line\Payment\Api\Data\Checkout\PaymentAttributeInterface;
use Line\Payment\Api\Request\BuilderInterface;
use Line\Payment\Api\ResolveInstallmentPlanActionInterface;
use Line\Payment\Gateway\DataReader;
use Line\Payment\Model\Checkout\SensitiveDataRegistry;
use Line\Payment\Model\GetTransactionIdentifierAction;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Psr\Log\LoggerInterface;

class DetailsDataBuilder implements BuilderInterface
{
    private DataReader $reader;
    private GetTransactionIdentifierAction $identifier;
    private SensitiveDataRegistry $registry;
    private ResolveInstallmentPlanActionInterface $resolver;
    private LoggerInterface $logger;

    /**
     * @param DataReader $reader
     * @param GetTransactionIdentifierAction $action
     * @param SensitiveDataRegistry $registry
     * @param ResolveInstallmentPlanActionInterface $resolver
     * @param LoggerInterface $logger
     */
    public function __construct(
        DataReader $reader,
        GetTransactionIdentifierAction $action,
        SensitiveDataRegistry $registry,
        ResolveInstallmentPlanActionInterface $resolver,
        LoggerInterface $logger
    ) {
        $this->reader = $reader;
        $this->identifier = $action;
        $this->registry = $registry;
        $this->resolver = $resolver;
        $this->logger = $logger;
    }

    /**
     *
     * @throws LocalizedException
     */
    public function build(array $buildSubject): array
    {
        /** @var PaymentDataObjectInterface $payment */
        $payment = $this->reader->readPayment($buildSubject);

        /** @TODO: improve validation since Braintree made a _lovely_ overwrite of OrderAdapter class */
        /** @var OrderAdapterInterface $order */
        $order = $payment->getOrder();

        $info = $payment->getPayment();

        // Customer Identifier for this transaction
        $identifier = $this->identifier->generate($info);

        // Reference value
        $incrementId = $order->getOrderIncrementId();

        $installments = (int) $info->getAdditionalInformation(PaymentAttributeInterface::PAYMENT_INSTALLMENTS);
        $claimedMerchant = $info->getAdditionalInformation(PaymentAttributeInterface::PAYMENT_MERCHANT_NUMBER);
        $card = $this->registry->get();

        if ($card === null) {
            $this->logger->error(
                'Line Payment: no card data in the request scope while resolving the installment plan.',
                ['order_increment_id' => $incrementId]
            );

            throw new LocalizedException(
                __('We can\'t process this payment right now. Please re-enter your card details and try again.')
            );
        }

        $plan = $this->resolver->resolve(
            $card->getBin(),
            (string) $info->getAdditionalInformation(PaymentAttributeInterface::CREDIT_CARD_TYPE),
            $installments,
            is_scalar($claimedMerchant) ? (string) $claimedMerchant : null
        );

        // Total amount to charge, including the surcharge of the plan the server resolved.
        $amount = round($order->getGrandTotalAmount() * $plan->getRate(), 2);

        // Persist the authoritative rate so the admin view and any downstream consumer read the
        // number the gateway was actually charged with, not the one the browser proposed.
        $info->setAdditionalInformation(
            PaymentAttributeInterface::PAYMENT_INSTALLMENT_RATE,
            $plan->getRate()
        );

        // Details data structure
        $details = [
            self::FIELD_DETAIL_BUSINESS_NUMBER => $plan->getMerchantNumber(),
            self::FIELD_DETAIL_AMOUNT => $amount,
            self::FIELD_DETAIL_INSTALLMENTS => $plan->getInstallments(),
            self::FIELD_CUSTOMER_IDENTIFIER => $identifier,
            self::FIELD_REFERENCE => $incrementId
        ];

        return [
            self::FIELD_DETAIL => [$details]
        ];
    }
}
