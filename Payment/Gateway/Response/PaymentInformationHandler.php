<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Gateway\Response;

use InvalidArgumentException;
use Line\Payment\Api\Data\Checkout\PaymentAttributeInterface;
use Line\Payment\Api\Data\ConfigInterface;
use Line\Payment\Api\ResponseInterface;
use Line\Payment\Gateway\DataReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;

/**
 *
 */
class PaymentInformationHandler implements HandlerInterface
{
    /**
     * Fields that will be removed from additional_information for security reasons
     * @var string[]
     */
    private const ADDITIONAL_FIELDS_TO_REMOVE = [
        PaymentAttributeInterface::CREDIT_CARD_CVV,
        PaymentAttributeInterface::CREDIT_CARD_NUMBER
    ];

    /**
     * @var DataReader $reader
     */
    private DataReader $reader;

    /**
     * @var ConfigInterface $config
     */
    private ConfigInterface $config;

    /**
     * @param DataReader $reader
     */
    public function __construct(
        DataReader $reader,
        ConfigInterface $config
    ) {
        $this->reader = $reader;
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $object = $this->reader->readPayment($handlingSubject);

        /** @var ResponseInterface $transaction */
        $transaction = $this->reader->readTransaction($response);

        /**
         * @TODO: evaluate setting up `getIsTransactionPending` based on response
         * @see \Magento\Sales\Model\Order\Payment\State\CaptureCommand
         */

        /** @var OrderPaymentInterface|InfoInterface $payment */
        $payment = $object->getPayment();

        $this->setCreditCardInformation($payment, $transaction);
        $this->setExpirationDate($payment);
        $this->setCreditCardStatus($payment, $transaction);
        $this->setInstallments($payment, $transaction);
        $this->setAuthorizationCode($payment, $transaction);

        $this->removeAdditionalInformation($payment);

        // sets temporary status to the Order
        $this->setOrderStatus($payment);
    }

    /**
     * Remove sensitive fields from Payment Additional Information
     *
     * @param $payment
     */
    public function removeAdditionalInformation($payment)
    {
        foreach (self::ADDITIONAL_FIELDS_TO_REMOVE as $key) {
            $payment->unsAdditionalInformation($key);
        }
    }

    /**
     * Sets credit card info into additional information
     */
    public function setCreditCardInformation(
        $payment,
        ResponseInterface $response
    ) {
        $numbers = $response->getCreditCardNumber();
        $brand = $response->getCreditCardBrand();

        $payment->setCcLast4(substr($numbers, -4));

        $payment->setAdditionalInformation(
            PaymentAttributeInterface::CREDIT_CARD_TYPE,
            $brand
        );
    }

    /**
     * Sets expiration date into the additional information
     */
    public function setExpirationDate($payment) {
        $additional = $payment->getAdditionalInformation();

        if ($month = $additional[PaymentAttributeInterface::CREDIT_CARD_EXP_MONTH]) {

            $month = (int) $month < 10
                ? '0' . $month
                : $month;

            $payment->setCcExpMonth($month);

        }

        if ($year = $additional[PaymentAttributeInterface::CREDIT_CARD_EXP_YEAR]) {
            $payment->setCcExpYear($year);
        }
    }

    /**
     * @param $payment
     * @param ResponseInterface $response
     */
    public function setCreditCardStatus(
        $payment,
        ResponseInterface $response
    ) {
        try {
            $status = $response->getErrorCode();

            // @TODO: get the status message from `error_mapping.xml` using the error code
            // AUTORIZADA = 0, NOAUTORIZADA = X
            $formatted = $response->getFormattedMessage();

            $payment->setCcStatus($status);
            $payment->setCcStatusDescription($formatted);

            // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock
        } catch (InvalidArgumentException $exception) {
            // pass
        }
    }


    /**
     * @param $payment
     * @param ResponseInterface $response
     */
    public function setInstallments(
        $payment,
        ResponseInterface $response
    ) {
        try {
            $installments = $response->getDetail()->getInstallments();

            $payment->setAdditionalInformation(
                PaymentAttributeInterface::PAYMENT_INSTALLMENTS,
                $installments
            );

            // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock
        } catch (InvalidArgumentException $exception) {
            // pass
        }
    }

    /**
     * @param $payment
     * @param ResponseInterface $response
     */
    public function setAuthorizationCode(
        $payment,
        ResponseInterface $response
    ) {
        try {
            $auth = $response->getDetail()->getAuthorizationCode();

            $payment->setAdditionalInformation(
                PaymentAttributeInterface::CREDIT_CARD_AUTORIZATION_CODE,
                $auth
            );

            // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock
        } catch (InvalidArgumentException $exception) {
            // pass
        }
    }

    /**
     * Sets a temporal status to the order
     */
    public function setOrderStatus($payment)
    {
        $order = $payment->getOrder();

        $status = $this->config->getOrderStatus();

        $order->setStatus($status);
        $comment = __('Order Placed by Line Payment');
        $order->addStatusHistoryComment($comment, $payment->getOrder()->getStatus());
    }
}
