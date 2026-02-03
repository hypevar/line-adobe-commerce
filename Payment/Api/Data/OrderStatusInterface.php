<?php
/**
 * Copyright © 2024 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Api\Data;

use Magento\Sales\Model\Order;

/**
 * Expose and centralize all available states to be internally used
 */
interface OrderStatusInterface
{
    /**#@+
     * @var string
     * @access public
     */
    public const STATE_NEW = Order::STATE_NEW;
    public const STATE_PENDING_PAYMENT = Order::STATE_PENDING_PAYMENT;
    public const STATE_PAYMENT_REVIEW = Order::STATE_PAYMENT_REVIEW;
    public const STATE_PROCESSING = Order::STATE_PROCESSING;
    public const STATE_HOLDED = Order::STATE_HOLDED;
    public const STATE_COMPLETE = Order::STATE_COMPLETE;
    public const STATE_CLOSED = Order::STATE_CLOSED;
    public const STATE_CANCELED = Order::STATE_CANCELED;
    /**#@-*/
}
