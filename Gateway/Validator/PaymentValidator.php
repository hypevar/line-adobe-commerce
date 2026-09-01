<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Gateway\Validator;

use Line\Payment\Api\Response\AttributeDetailInterface;
use Line\Payment\Api\Response\AttributeInterface;
use Line\Payment\Api\Response\ErrorCodeInterface;
use Line\Payment\Api\Response\StatusCodeInterface;
use Line\Payment\Api\Response\StatusInterface;
use Line\Payment\Api\Response\ValidatorInterface;
use Magento\Framework\DataObject;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;

/**
 * Decides whether the gateway actually authorized the transaction.
 *
 * Every field the gateway uses to say "no" is checked, not just the top level error code:
 * `Estado`, `CodigoEstado` and the per-transaction `Detalle[0].CodigoError` can each reject an
 * otherwise well-formed envelope, and a response missing any of them is treated as a rejection.
 */
class PaymentValidator extends AbstractValidator implements ValidatorInterface
{
    /**
     * @inheritDoc
     */
    public function validate(array $validationSubject): ResultInterface
    {
        $errorMessages = [];
        $errorCodes = [];

        // if no code is coming, we'll force to be detected as one
        $errorCode = $validationSubject[AttributeInterface::FIELD_ERROR_CODE]
            ?? ErrorCodeInterface::CODE_ERROR;

        $status = $validationSubject[AttributeInterface::FIELD_STATUS]
            ?? StatusInterface::STATUS_ERROR;

        $statusCode = $validationSubject[AttributeInterface::FIELD_STATUS_CODE]
            ?? StatusCodeInterface::STATUS_CODE_REJECTED;

        $detailErrorCode = $this->getDetailErrorCode($validationSubject);
        $normalizedStatus = strtoupper(trim((string) $status));
        $normalizedStatusCode = strtolower(trim((string) $statusCode));

        $isValid = $this->isAuthorizedCode($errorCode)
            && $normalizedStatus === StatusInterface::STATUS_AUTHORIZED
            && $normalizedStatusCode === StatusCodeInterface::STATUS_CODE_APPROVED
            && ($detailErrorCode === null || $this->isAuthorizedCode($detailErrorCode));

        if (!$isValid) {
            $errorMessages[] = $status;

            if (isset($validationSubject[AttributeInterface::FIELD_FORMATED_MESSAGE])) {
                $errorMessages[] = $validationSubject[AttributeInterface::FIELD_FORMATED_MESSAGE];
            }

            $errorCodes[] = $this->isAuthorizedCode($errorCode) && $detailErrorCode !== null
                ? $detailErrorCode
                : $errorCode;
        }

        return $this->createResult($isValid, $errorMessages, $errorCodes);
    }

    /**
     * The gateway sends the authorization code as a JSON number, but a proxy or a re-encode can
     * turn it into the string "0". Comparing identically against the integer 0 declined those.
     *
     * @param mixed $code
     *
     * @return bool
     */
    private function isAuthorizedCode($code): bool
    {
        return is_numeric($code) && (int) $code === ErrorCodeInterface::CODE_AUTHORIZED;
    }

    /**
     * @param array $validationSubject
     *
     * @return mixed|null null when the response carries no detail node
     */
    private function getDetailErrorCode(array $validationSubject)
    {
        $detail = $validationSubject[AttributeInterface::FIELD_DETAIL] ?? null;

        if ($detail instanceof DataObject) {
            return $detail->getData(AttributeDetailInterface::FIELD_ERROR_CODE);
        }

        if (is_array($detail)) {
            return $detail[AttributeDetailInterface::FIELD_ERROR_CODE] ?? null;
        }

        return null;
    }
}
