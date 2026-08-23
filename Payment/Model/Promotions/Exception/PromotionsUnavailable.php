<?php
/**
 * Copyright © 2025 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Model\Promotions\Exception;

use Magento\Framework\Exception\LocalizedException;

/**
 * The promotions service could not be reached, or answered with something unusable.
 *
 * This is not the same as "this card has no promotions": no promotions is a legitimate answer and
 * resolves to a single installment at rate 1.0. This exception means we do not know the rate, and
 * since the rate decides how much money is charged, the order must not be placed.
 */
class PromotionsUnavailable extends LocalizedException
{
}
