<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Gateway\DataResolver;

/**
 * Generates the Credit Card expiration date value
 * as the Gateway requires
 *
 * @api
 * @since 0.1.0
 */
class ExpirationDateResolver
{
    /**
     * Returns the exp date in YYMM format
     *
     * @param string $year
     * @param string $month
     *
     * @return string
     */
    public function get(string $year, string $month): string
    {
        // gateway requires only two digits
        $expirationYear = strlen($year) === 4
            ? substr($year, 2, 2)
            : $year;

        // gateway requires two digits
        $expirationMonth = strlen($month) === 1
            ? '0' . $month
            : $month;

        return $expirationYear . $expirationMonth;
    }
}
