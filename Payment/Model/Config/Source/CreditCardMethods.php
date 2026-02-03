<?php
/*
 * Copyright © 2025 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Credit Card Methods (Credit/Debit) source
 */
class CreditCardMethods implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'CREDIT', 'label' => __('Credit')],
            ['value' => 'DEBIT', 'label' => __('Debit')],
        ];
    }
}
