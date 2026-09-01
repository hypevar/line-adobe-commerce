<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Model\Config\Source;

use Line\Payment\Api\Data\ConfigInterface;

class Modes
{
    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => '', 'label' => __('-- Please Select --')],
            ['value' => ConfigInterface::MODE_SANDBOX_VALUE, 'label' => __('Sandbox')],
            ['value' => ConfigInterface::MODE_PRODUCTION_VALUE, 'label' => __('Production')],
            ['value' => ConfigInterface::MODE_MOCK_VALUE, 'label' => __('Mock (offline fixtures)')]
        ];
    }
}
