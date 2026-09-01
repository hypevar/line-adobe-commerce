<?php
/**
 * Copyright © 2024 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Api;

use Line\Payment\Api\PromotionsInterface;

interface GetPromotionsByBinActionInterface
{
    public const PARAM_BIN_NAME = 'bin';
    public const ENDPOINT_URL = '/payment/marketplace/%s/%s/status/active';

    /**
     * @param string $value BIN value to be sent to the Gateway
     *
     * @return PromotionsInterface[]
     */
    public function get(string $value): array;
}
