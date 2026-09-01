<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Model\Config\Source;

use Line\Payment\Api\Data\Config\SSLVersionsInterface;

/**
 *
 */
class SSLVersions
{
    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        $list = SSLVersionsInterface::SSL_VERSIONS_OPTIONS_LIST;

        // @TODO: double check if makes sense to have int as values
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
