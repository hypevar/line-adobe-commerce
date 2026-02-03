<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Gateway\DataResolver;

use Line\Payment\Api\Response\ErrorCodeInterface;
use Line\Payment\Api\Request\StatusInterface;

/**
 * @api
 * @since 0.1.0
 */
class ErrorCodeResolver
{
    /**
     * Retrieves the error label and description based on the error code provided
     *
     * @param int $code error code value
     *
     * @return array
     */
    public function get(int $code): array
    {
        /**
         * Try to match the easy ones
         */
        $name = match ($code) {
            ErrorCodeInterface::CODE_ERROR => [
                ErrorCodeInterface::ARRAY_KEY_LABEL => StatusInterface::STATUS_ERROR,
                ErrorCodeInterface::ARRAY_KEY_DESCRIPTION => StatusInterface::STATUS_ERROR
            ],
            ErrorCodeInterface::CODE_AUTHORIZED => [
                ErrorCodeInterface::ARRAY_KEY_LABEL => StatusInterface::STATUS_AUTHORIZED,
                ErrorCodeInterface::ARRAY_KEY_DESCRIPTION => StatusInterface::STATUS_AUTHORIZED
            ],
            default => ''
        };

        // Case where Status is UNAUTHORIZED, and Gateway sends the custom ISO code...
        if (empty($name)) {
            $list = ErrorCodeInterface::ERROR_CODE_LIST;

            $name = isset($list[$code])
                ? $list[$code]
                : $name;
        }

        return $name;
    }
}
