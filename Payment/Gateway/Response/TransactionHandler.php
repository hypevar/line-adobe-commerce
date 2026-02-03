<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Gateway\Response;

use Line\Payment\Api\Response\AttributeDetailInterface;
use Line\Payment\Api\Response\AttributeInterface;
use Line\Payment\Api\Response\HandlerInterface;
use Line\Payment\Gateway\DataReader;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Transaction;

/**
 *
 */
class TransactionHandler implements HandlerInterface
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
     * @param array $handlingSubject
     * @param array $gatewayResponse
     */
    public function handle(array $handlingSubject, array $gatewayResponse)
    {
        $object = $this->reader->readPayment($handlingSubject);

        /** @var Payment $payment */
        $payment = $object->getPayment();

        if (!$payment instanceof Payment) {
            return;
        }

        $transactionDO = $this->reader->readTransaction($gatewayResponse);

        if (!isset($transactionDO[AttributeInterface::FIELD_IDENTIFIER])) {
            // request has failed
            // we don't need a transaction id
            return;
        }

        $identifier = $transactionDO[AttributeInterface::FIELD_IDENTIFIER];
        $clientIdentifier = $transactionDO[AttributeInterface::FIELD_CUSTOMER_IDENTIFIER];

        // used against gateway for refunds/voids
        $payment->setCcTransId($clientIdentifier);

        /**
         * we don't have to set the last transaction id
         * Transaction Builder, will check if there's a transaction_id configured
         * and create one (since there isn't), whilst assigns `setLastTransId`
         */
        $payment->setTransactionId($identifier);

        // add information to the current Transaction
        $this->addTransactionDetails($payment, $transactionDO);

        $payment->setIsTransactionClosed($this->shouldCloseTransaction());
        $closed = $this->shouldCloseParentTransaction($payment);
        $payment->setShouldCloseParentTransaction($closed);
    }

    /**
     * Whether transaction should be closed
     *
     * @return bool
     */
    protected function shouldCloseTransaction(): bool
    {
        return true;
    }

    /**
     * Whether parent transaction should be closed
     *
     * @param Payment $payment
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function shouldCloseParentTransaction(Payment $payment): bool
    {
        return true;
    }

    /**
     * @param Payment $payment
     * @param $data
     */
    public function addTransactionDetails(Payment $payment, $data)
    {
        $raw = [];
        $raw[AttributeInterface::FIELD_IDENTIFIER] = $data->getIdentifier();
        $raw[AttributeInterface::FIELD_CUSTOMER_IDENTIFIER] = $data->getCustomerIdentifier();
        $raw[AttributeInterface::FIELD_MESSAGE] = $data->getMessage();
        $raw[AttributeInterface::FIELD_FORMATED_MESSAGE] = $data->getFormattedMessage();
        $raw[AttributeInterface::FIELD_STATUS] = $data->getStatus();
        $raw[AttributeInterface::FIELD_ERROR_CODE] = $data->getErrorCode();
        $raw[AttributeInterface::FIELD_STATUS_CODE] = $data->getStatusCode();

        /** @var AttributeDetailInterface $details */
        $details = $data->getDetail();
        $raw['details_' . AttributeDetailInterface::FIELD_DATE] = $details->getDate();
        $raw['details_' . AttributeDetailInterface::FIELD_OPERATION_TYPE] = $details->getOperationType();
        $raw['details_' . AttributeDetailInterface::FIELD_STATUS] = $details->getStatus();
        $raw['details_' . AttributeDetailInterface::FIELD_AUTH_CODE] = $details->getAuthorizationCode();
        $raw['details_' . AttributeDetailInterface::FIELD_INSTALLMENTS] = $details->getInstallments();
        $raw['details_' . AttributeDetailInterface::FIELD_ERROR_CODE] = $details->getErrorCode();
        $raw['details_' . AttributeDetailInterface::FIELD_ERROR_STATUS] = $details->getStatusCode();

        // set all details into the right
        $payment->setTransactionAdditionalInfo(
            Transaction::RAW_DETAILS,
            $raw
        );
    }
}
