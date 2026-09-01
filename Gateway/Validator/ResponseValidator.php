<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Gateway\Validator;

use Line\Payment\Api\Response\AttributeInterface;
use Line\Payment\Api\Response\ValidatorInterface;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;

/**
 *
 */
class ResponseValidator extends AbstractValidator implements ValidatorInterface
{
    /**
     * Matches basic error types from `error_mapping.xml` file
     *
     * @var array
     */
    private const ERROR_CODES = [
        'error_has_occurred' => 999,
        'malformed_response_object' => 000
    ];

    /**
     * @inheritDoc
     */
    public function validate(array $validationSubject): ResultInterface
    {
        $isValid = true;
        $errorMessages = [];
        $errorCodes = [];

        /**
         * Something happened within the Connector
         * Given it's been unable to create an object after making the request
         *
         * @see \Line\Payment\Model\Client\Connector
         */
         if (!isset($validationSubject[AttributeInterface::FIELD_IDENTIFIER])) {
            $isValid = false;

            // check if we can return customized message
            if (isset($validationSubject[AttributeInterface::FIELD_MESSAGE])) {
                $errorMessages[] = $validationSubject[AttributeInterface::FIELD_MESSAGE];

                // won't set the code unless it comes
                // so plain error messages can be picked up from `error_mapping` xml file
                if (isset($validationSubject[AttributeInterface::FIELD_ERROR_CODE])) {
                    $errorCodes[] = $validationSubject[AttributeInterface::FIELD_ERROR_CODE];
                }

            } else {
                $errorMessages[] = __('Gateway Connector Error: `identifier` is missing in response');
                $errorCodes[] = self::ERROR_CODES['malformed_response_object'];
            }
        }

        return $this->createResult($isValid, $errorMessages, $errorCodes);
    }
}
