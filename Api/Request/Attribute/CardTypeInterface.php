<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Api\Request\Attribute;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 *
 */
interface CardTypeInterface
{
    /**#@+
     * @access public
     * @var string
     */
    public const TYPE_CREDIT = 'CREDITO';
    public const TYPE_DEBIT = 'DEBITO';
    /**#@-*/

    /**#@+
     * @access public
     * @var array
     */
    public const LIST_CREDIT = [
        CardCodeInterface::AMEX,
        CardCodeInterface::MASTERCARD,
        CardCodeInterface::VISA,
    ];

    public const LIST_DEBIT = [];
    /**#@-*/
}
