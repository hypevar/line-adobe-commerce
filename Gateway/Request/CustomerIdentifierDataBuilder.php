<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Gateway\Request;

use Line\Payment\Api\Request\BuilderInterface;
use Line\Payment\Gateway\DataReader;
use Line\Payment\Model\GetTransactionIdentifierAction;
use PayPal\Braintree\Gateway\Data\Order\OrderAdapter;

/**
 *
 */
class CustomerIdentifierDataBuilder implements BuilderInterface
{
    private DataReader $reader;
    private GetTransactionIdentifierAction $identifier;

    /**
     * @param DataReader $reader
     * @param GetTransactionIdentifierAction $action
     */
    public function __construct(
        DataReader $reader,
        GetTransactionIdentifierAction $action
    ) {
        $this->reader = $reader;
        $this->identifier = $action;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject): array
    {
        $payment = $this->reader->readPayment($buildSubject);

        // generate custom identifier for this particular Order
        $identifier = $this->identifier->generate($payment->getPayment());

        return [
            self::FIELD_CUSTOMER_IDENTIFIER => $identifier
        ];
    }
}
