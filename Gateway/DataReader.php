<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Gateway;

use InvalidArgumentException;
use Line\Payment\Api\ResponseInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;

class DataReader
{
    /**
     * @param array $subject
     *
     * @return array
     */
    public function readResponse(array $subject): array
    {
        return SubjectReader::readResponse($subject);
    }

    /**
     * @param array $subject
     *
     * @return array
     */
    public function readResponseObject(array $subject)
    {
        if (!isset($subject['object'])) {
            throw new InvalidArgumentException('Response object does not exist');
        }

        return $subject['object'];
    }

    /**
     * @param array $subject
     *
     * @return PaymentDataObjectInterface
     */
    public function readPayment(array $subject): PaymentDataObjectInterface
    {
        return SubjectReader::readPayment($subject);
    }

    /**
     * @param array $subject
     *
     * @return ResponseInterface
     */
    public function readTransaction(array $subject): ResponseInterface
    {
        $response = $this->readResponseObject($subject);

        // Ensure we're dealing with the response object
        // and nothing got silently killed
        if (!$response instanceof ResponseInterface) {
            throw new InvalidArgumentException('Object is not a ResponseInterface.');
        }

        // Ensure also, we've at least a message
        /** @var ResponseInterface $response */
        if (!$response->getMessage()) {
            throw new InvalidArgumentException('Response is not a valid type.');
        }

        return $response;
    }

    /**
     * @param array $subject
     *
     * @return mixed
     */
    public function readAmount(array $subject)
    {
        return SubjectReader::readAmount($subject);
    }
}
