<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Api\Request;

use Magento\Payment\Gateway\Request\BuilderInterface as PaymentBuilderInterface;

/**
 * Request Builder interface
 */
interface BuilderInterface extends PaymentBuilderInterface, AttributeInterface
{
    // silence is golden
}
