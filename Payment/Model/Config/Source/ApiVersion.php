<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Model\Config\Source;

/**
 *
 */
class ApiVersion
{
    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'v1', 'label' => __('Version 1')],
        ];
    }
}
