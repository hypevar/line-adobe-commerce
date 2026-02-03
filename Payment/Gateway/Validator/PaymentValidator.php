<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Gateway\Validator;

use Line\Payment\Api\Response\ErrorCodeInterface;
use Line\Payment\Api\Response\StatusCodeInterface;
use Line\Payment\Api\Response\StatusInterface;
use Line\Payment\Api\Response\AttributeInterface;
use Line\Payment\Api\Response\ValidatorInterface;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;

/**
 *
 */
class PaymentValidator extends AbstractValidator implements ValidatorInterface
{
    /**
     * @inheritDoc
     */
    public function validate(array $validationSubject): ResultInterface
    {
        $isValid = true;
        $errorMessages = [];
        $errorCodes = [];

        // if no code is coming, we'll force to be detected as one
        $errorCode = isset($validationSubject[AttributeInterface::FIELD_ERROR_CODE])
            ? $validationSubject[AttributeInterface::FIELD_ERROR_CODE]
            : ErrorCodeInterface::CODE_ERROR;

        $status = isset($validationSubject[AttributeInterface::FIELD_STATUS])
            ? $validationSubject[AttributeInterface::FIELD_STATUS]
            : StatusInterface::STATUS_ERROR;

        $statusCode = isset($validationSubject[AttributeInterface::FIELD_STATUS_CODE])
            ? $validationSubject[AttributeInterface::FIELD_STATUS_CODE]
            : StatusCodeInterface::STATUS_CODE_REJECTED;

        if ($errorCode !== 0) {
            $isValid = false;
            $errorMessages[] = $status;
            $errorMessages[] = $validationSubject[AttributeInterface::FIELD_FORMATED_MESSAGE];
            $errorCodes[] = $errorCode;
        }

        return $this->createResult($isValid, $errorMessages, $errorCodes);
    }
}
