<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */
declare(strict_types=1);

namespace Line\Payment\Gateway\Http\Client;

class TransactionCapture extends AbstractTransaction
{
    /**
     * @inheritdoc
     */
    protected function process(array $data)
    {
        // @TODO: implement Capture command
        $operationId = '';
        return $this->adapter->capture($operationId, $data);
    }
}
