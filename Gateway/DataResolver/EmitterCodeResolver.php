<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Gateway\DataResolver;

use Line\Payment\Api\Request\Attribute\CardCodeInterface;

/**
 *
 * @api
 * @since 0.1.0
 */
class EmitterCodeResolver
{
    /**
     * Receives Checkout Credit Card Code and returns Gateway known cc code
     * If missing, returns an empty string
     *
     * @param string $cardCode Checkout CC code
     *
     * @return string
     */
    public function get(string $cardCode): string
    {
        $code = '';
        $internal = CardCodeInterface::CODE_LIST;

        foreach ($internal as $emitterCode) {
            if ($cardCode === $emitterCode['value']) {
                return $emitterCode['value'];
            }
        }

        return $code;
    }
}
