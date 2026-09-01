<?php
/**
 * Copyright © 2024 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Api;

use Line\Payment\Api\PromotionsInterface;

interface GetPromotionsActionInterface
{
    public const ENDPOINT_URL = '/payment/marketplace/%s/%s/status/active';

    /**
     * @param string $cardBrand
     *
     * @return PromotionsInterface[]
     */
    public function get(string $cardBrand = ''): array;
}
