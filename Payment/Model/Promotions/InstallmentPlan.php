<?php
/**
 * Copyright © 2025 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Model\Promotions;

/**
 * The installment plan the server resolved for an authorization. Decides the charged amount.
 */
class InstallmentPlan
{
    private int $installments;
    private float $rate;
    private string $merchantNumber;

    /**
     * @param int $installments
     * @param float $rate
     * @param string $merchantNumber
     */
    public function __construct(
        int $installments,
        float $rate,
        string $merchantNumber
    ) {
        $this->installments = $installments;
        $this->rate = $rate;
        $this->merchantNumber = $merchantNumber;
    }

    /**
     * @return int
     */
    public function getInstallments(): int
    {
        return $this->installments;
    }

    /**
     * Coefficient applied to the grand total. 1.0 means no surcharge.
     *
     * @return float
     */
    public function getRate(): float
    {
        return $this->rate;
    }

    /**
     * @return string
     */
    public function getMerchantNumber(): string
    {
        return $this->merchantNumber;
    }
}
