<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Gateway\Response;

use Line\Payment\Api\ResponseInterface;
use Line\Payment\Gateway\DataReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Psr\Log\LoggerInterface;

/**
 *
 */
class DebuggerHandler implements HandlerInterface
{
    private DataReader $reader;
    private LoggerInterface $logger;

    /**
     * @param DataReader $reader
     */
    public function __construct(
        DataReader $reader,
        LoggerInterface $logger
    ) {
        $this->reader = $reader;
        $this->logger = $logger;
    }

    /**
     * @param array $handlingSubject
     * @param array $response
     */
    public function handle(array $handlingSubject, array $response)
    {
        $object = $this->reader->readPayment($handlingSubject);

        /** @var OrderPaymentInterface $payment */
        $payment = $object->getPayment();

        if ($payment instanceof OrderPaymentInterface) {
            /** @var ResponseInterface $paymentResponse */
            $paymentResponse = $this->reader->readTransaction($response);

            $payment->setCcDebugResponseBody(
                json_encode([
                    'message' => $paymentResponse->getMessage(),
                    'error_code' => $paymentResponse->getErrorCode(),
                    'status' => $paymentResponse->getStatus(),
                    'status_code' => $paymentResponse->getStatusCode()
                ])
            );
        }
    }
}
