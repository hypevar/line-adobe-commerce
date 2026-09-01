<?php
/**
 * Copyright © 2024 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Model\Config\Source\Promotions;

/**
 * Provides configuration options for Promotion Marketplace value
 */
class Marketplace
{
    /**
     * Returns available Marketplaces
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            'value' => 'magento', 'label' => __('Adobe Commerce, Magento Open Source')
        ];
    }
}
