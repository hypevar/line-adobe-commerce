<?php
/**
 * Copyright © 2026 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Model\Sandbox;

/**
 * Maps a gateway request path onto the fixture directory that stands in for it.
 */
class OperationResolver
{
    private const OPERATIONS = [
        '#/creditcard/autorizacion#' => 'sale',
        '#/creditcard/anulacion/#' => 'refund',
        '#/creditcard/emisores#' => 'emitters',
        '#/status/active\?.*bin=#' => 'promotions-by-bin',
        '#/status/active#' => 'promotions'
    ];

    /**
     * Names the operation a request path belongs to, or null when it is not mocked.
     *
     * @param string $path
     *
     * @return string|null
     */
    public function resolve(string $path): ?string
    {
        foreach (self::OPERATIONS as $pattern => $operation) {
            if (preg_match($pattern, $path) === 1) {
                return $operation;
            }
        }

        return null;
    }
}
