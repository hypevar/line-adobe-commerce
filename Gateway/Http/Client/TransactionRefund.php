<?php
/**
 *
 */

declare(strict_types=1);

namespace Line\Payment\Gateway\Http\Client;

use Line\Payment\Api\Request\RefundInterface;

/**
 * Executes a Refund Gateway operation
 */
class TransactionRefund extends AbstractTransaction
{
    /**
     * @inheritdoc
     */
    protected function process(array $data)
    {
        return $this->adapter->refund(
            $data[RefundInterface::FIELD_IDENTIFIER]
        );
    }
}
