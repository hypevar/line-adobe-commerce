<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Gateway\Http;

use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\Http\TransferInterface;

/**
 *
 */
class TransferFactory implements TransferFactoryInterface
{
    private TransferBuilder $transferBuilder;

    /**
     * @param TransferBuilder $transferBuilder
     */
    public function __construct(TransferBuilder $transferBuilder)
    {
        $this->transferBuilder = $transferBuilder;
    }

    /**
     * @param array $request
     *
     * @return TransferInterface
     */
    public function create(array $request)
    {
        return $this->transferBuilder->setBody($request)->build();
    }
}
