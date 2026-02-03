<?php
/**
 * Copyright © 2024 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Model\Promotions;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Psr\Log\LoggerInterface;

/**
 * Reshapes Gateway's Promotion response into an easy access data structure
 */
class DataConverter
{
    private LoggerInterface $logger;
    private TimezoneInterface $timezone;

    public function __construct(
        TimezoneInterface $timezone,
        LoggerInterface $logger
    ) {
        $this->timezone = $timezone;
        $this->logger = $logger;
    }

    /**
     * @param array $payload JSON object coming from Gateway's response
     *
     * @return array
     */
    public function convert(array $payload): array
    {
        if (!isset($payload['brands'])) {
            return [];
        }

        $promosByBrands = $payload['brands'];

        $brand = current($promosByBrands);

        $promotions = [];
        $cardBrand = $brand['cardBrand'];
        $defaultMerchant = $brand['defaultMerchant'] ?? false;

        unset($defaultMerchant['activationKey']);

        $binData = $installmentsData = $merchantData = [];

        foreach($brand['options'] as $promo) {
            // ensure Promotion is available
            if (!$this->isAvailable($promo)) {
                $this->logger->debug('Promotion is not available (enabled, start-end date or weekday)', $promo);
                continue;
            }

            $bin = $promo['bank']['bines'][0];

            $binData = [
                'number' => $bin['number'],
                'cardType' => $bin['cardType']
            ];

            $merchantData = $promo['merchant'];
            $installmentsData = [];

            foreach ($promo['installments'] as $installment) {
                // avoid DEBIT option as installment
                if (trim($installment['name']) === "DEBITO"
                    || trim($installment['displayName']) === "DEBITO") {
                    continue;
                }
                unset($installment['id']);
                $installmentsData[] = $installment;
            }

            $promotions[] = [
                'bin' => $binData,
                'merchant' => $merchantData,
                'installments' => $installmentsData
            ];
        }

        return [
            'promotions' => $promotions,
            'cardBrand' => $cardBrand,
            'defaultMerchant' => $defaultMerchant
        ];
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
