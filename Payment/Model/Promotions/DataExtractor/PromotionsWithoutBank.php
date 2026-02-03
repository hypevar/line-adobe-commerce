<?php
/**
 * Copyright © 2025 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Model\Promotions\DataExtractor;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Returns all Promotions that applies to a specific brand but for all banks
 * example: All VISA from any bank
 */
class PromotionsWithoutBank
{
    private TimezoneInterface $timezone;

    public function __construct(
        TimezoneInterface $timezone
    ) {
        $this->timezone = $timezone;
    }

    public function extractByCardBrand(string $cardBrand, array $payload): array
    {
        $result = [];
        $brands = $payload['brands'];

        foreach ($brands as $card) {
            // if brand is the same
            if (isset($card['cardBrand']) && $card['cardBrand'] === $cardBrand) {
                // if contains promotions
                if (isset($card['options']) && count($card['options'])) {

                    $installments = [];

                    foreach ($card['options'] as $promotion) {
                        // check if installment plan does not contain bank
                        if (!isset($promotion['bank'])) {

                            if ($this->isAvailable($promotion)) {

                                // promos without bank sometimes comes without merchant
                                if (!isset($promotion['merchant'])) {
                                    $promotion['merchant'] = $card['defaultMerchant'];
                                }

                                foreach ($promotion['installments'] as $cuotas) {
                                    $installments[] = $cuotas;
                                }
                            }
                        }
                    }

                    $result = [
                        'promotions' => [
                            [
                                'installments' => $installments,
                                'merchant' => $card['defaultMerchant']
                            ]
                        ],
                        'cardBrand' => $cardBrand,
                        'defaultMerchant' => $card['defaultMerchant']
                    ];
                }
            }
        }

        return $result;
    }


    /**
     * Evaluates if Promotion should be displayed, based on a few properties
     *
     * it checks:
     *  - `enabled` (true/false)
     *  - `startDate` and `endDate` dates
     *  - `daysOfWeek` an array with days of the week
     */
    public function isAvailable(array $promo): bool
    {
        // ensure promo is enabled
        $isEnabled = (bool) $promo['enabled'] ?? false;

        if (!$isEnabled) {
            return false;
        }

        // Could be that values are not provided (comes as `null`)
        // In that case, we won't evaluate, cause values were not set (hence: without validity)
        $startDate = $promo['startDate'] ?? false;
        $endDate = $promo['endDate'] ?? false;
        $current = $this->timezone->date(null, null, true, false)->format('Y-m-d');

        if ($startDate) {
            $startDate = $this->timezone->date(new \DateTime($startDate))->format('Y-m-d');

            if ($startDate > $current) {
                return false;
            }
        }

        if ($endDate) {
            $endDate = $this->timezone->date(new \DateTime($endDate))->format('Y-m-d');

            if ($endDate < $current) {
                return false;
            }
        }

        // finally, check promotion is enabled in today's name
        return $this->isAvailableInWeekDay($promo);
    }

    /**
     * Check the day of the week to see if applies
     *
     * @param array $promo
     *
     * @return bool
     */
    public function isAvailableInWeekDay(array $promo): bool
    {
        $today = strtoupper(date('l'));

        return isset($promo['daysOfWeek']) && in_array($today, $promo['daysOfWeek']);
    }
}
