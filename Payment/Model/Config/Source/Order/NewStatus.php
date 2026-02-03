<?php
/**
 * Copyright © 2024 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Model\Config\Source\Order;

use Line\Payment\Api\Data\OrderStatusInterface;

/**
 * Returns all new and pending states that an Order can be set
 */
class NewStatus extends \Magento\Sales\Model\Config\Source\Order\Status
{
    /**
     * @var string[]
     */
    protected $stateStatuses = [
        OrderStatusInterface::STATE_NEW,
        OrderStatusInterface::STATE_PENDING_PAYMENT,
        OrderStatusInterface::STATE_PAYMENT_REVIEW
    ];
}
