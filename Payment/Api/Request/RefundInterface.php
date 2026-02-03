<?php
/**
 *
 */

declare(strict_types=1);

namespace Line\Payment\Api\Request;

/**
 * Represents payload for a Refund request
 */
interface RefundInterface
{
    /**#@+
     * @var string
     */
    const FIELD_IDENTIFIER = 'identifier';
    const FIELD_AMOUNT = 'amount';
    /**#@-*/
}
