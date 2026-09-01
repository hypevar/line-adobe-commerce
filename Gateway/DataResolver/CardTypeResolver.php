<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Gateway\DataResolver;

use Line\Payment\Api\Request\Attribute\CardTypeInterface;

/**
 * @api
 * @since 0.1.0
 */
class CardTypeResolver
{
    /**
     * Returns Card Type value based on the Credit Card Code
     * If Card is missing, will return an empty string
     *
     * @param string $code
     *
     * @return string
     */
    public function get(string $code): string
    {
        $type = '';
        $creditList = CardTypeInterface::LIST_CREDIT;
        $debitList = CardTypeInterface::LIST_DEBIT;

        if (in_array($code, $creditList)) {
            $type = CardTypeInterface::TYPE_CREDIT;

        } elseif (in_array($code, $debitList)) {
            $type = CardTypeInterface::TYPE_DEBIT;
        }

        return $type;
    }
}
