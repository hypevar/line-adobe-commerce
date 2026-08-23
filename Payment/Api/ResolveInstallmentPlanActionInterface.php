<?php
/**
 * Copyright © 2025 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Api;

use Line\Payment\Model\Promotions\Exception\PromotionsUnavailable;
use Line\Payment\Model\Promotions\InstallmentPlan;
use Magento\Framework\Exception\LocalizedException;

/**
 * Decides, server side, which installment plan an authorization runs under.
 *
 * The browser sends the number of installments it wants; everything that decides money — the rate
 * coefficient and the merchant number — is looked up here and never taken from the request.
 */
interface ResolveInstallmentPlanActionInterface
{
    /**
     * @param string $bin first six digits of the card, from the request-scoped card container
     * @param string $cardBrand brand reported at checkout
     * @param int $installments number of installments the customer selected
     * @param string|null $merchantNumber merchant number the browser claims, compared not trusted
     *
     * @return InstallmentPlan
     * @throws PromotionsUnavailable when the rate cannot be established
     * @throws LocalizedException when the submitted selection does not exist
     */
    public function resolve(
        string $bin,
        string $cardBrand,
        int $installments,
        ?string $merchantNumber = null
    ): InstallmentPlan;
}
