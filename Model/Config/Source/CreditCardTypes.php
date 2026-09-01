<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Model\Config\Source;

/**
 * Credit Cards list for backend configuration
 */
class CreditCardTypes
{
    /**
     * Returns available Credit Card list
     *
     * @return array
     */
    public function toOptionArray(): array
    {
         return [
            ['value' => '', 'label' => __('-- Please Select --')],
            ['value' => 'AMEX', 'label' => __('American Express')],
            ['value' => 'MASTERCREDITO', 'label' => __('MasterCard Crédito')],
            ['value' => 'MASTERDEBITO', 'label' => __('MasterCard Débito')],
            ['value' => 'VISACREDITO', 'label' => __('Visa Crédito')],
            ['value' => 'VISADEBITO', 'label' => __('Visa Débito')],
        ];

    }
}
