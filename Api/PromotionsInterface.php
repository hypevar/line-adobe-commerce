<?php
/**
 * Copyright © 2024 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Api;

use Line\Payment\Api\Data\PromotionInterface;
use Line\Payment\Api\Promotions\MerchantInformationInterface;

interface PromotionsInterface
{
    /**
     * @return PromotionInterface[]
     */
    public function getPromotions(): array;

    /**
     * @return MerchantInterface
     */
    public function getMerchantInformation(): MerchantInformationInterface;
}
