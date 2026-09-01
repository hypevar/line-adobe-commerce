<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Model\Config\Source;

use Line\Payment\Api\Data\Config\DocumentTypesInterface;

/**
 * Document Type list for backend configuration
 */
class DocumentTypes
{
    /**
     * Returns available Credit Card list
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $list = DocumentTypesInterface::DOCUMENT_TYPE_OPTIONS_LIST;

        $options = [];

        foreach ($list as $key => $label) {
            array_push(
                $options,
                ['value' => $key, 'label' => __($label)]
            );
        }

        return $options;
    }
}
