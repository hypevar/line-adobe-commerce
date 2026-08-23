<?php
/**
 * Copyright © 2025 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Model\Promotions;

use Line\Payment\Api\GetPromotionsActionInterface;
use Line\Payment\Api\GetPromotionsByBinActionInterface;
use Line\Payment\Api\ResolveInstallmentPlanActionInterface;
use Line\Payment\Model\Promotions\Exception\PromotionsUnavailable;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

/**
 * Server-side counterpart of the installment selection the checkout renders.
 *
 * Mirrors Line_Payment::js/model/promotions.js: look the BIN up, fall back to the promotions that
 * apply to the whole card brand, and fall back again to a single installment at no surcharge.
 *
 * It deliberately does NOT apply `installments_filter_configuration`. That filter runs in the
 * browser only; applying it here would reject plans the customer was legitimately shown.
 */
class ResolveInstallmentPlanAction implements ResolveInstallmentPlanActionInterface
{
    /**
     * Rate of a plan with no surcharge. Also the floor for any rate we accept.
     */
    private const NO_SURCHARGE_RATE = 1.0;

    /**
     * Ceiling for a rate coming off the promotions service.
     */
    private const MAX_RATE = 3.0;

    private GetPromotionsByBinActionInterface $promotionsByBin;
    private GetPromotionsActionInterface $promotions;
    private InstallmentPlanFactory $planFactory;
    private LoggerInterface $logger;

    /**
     * @param GetPromotionsByBinActionInterface $promotionsByBin
     * @param GetPromotionsActionInterface $promotions
     * @param InstallmentPlanFactory $planFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        GetPromotionsByBinActionInterface $promotionsByBin,
        GetPromotionsActionInterface $promotions,
        InstallmentPlanFactory $planFactory,
        LoggerInterface $logger
    ) {
        $this->promotionsByBin = $promotionsByBin;
        $this->promotions = $promotions;
        $this->planFactory = $planFactory;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        string $bin,
        string $cardBrand,
        int $installments,
        ?string $merchantNumber = null
    ): InstallmentPlan {
        if ($installments < 1) {
            throw new LocalizedException(
                __('The selected installment plan is no longer available. Please choose it again.')
            );
        }

        $payload = $this->promotionsByBin->get($bin);

        if (!isset($payload['promotions'])) {
            throw new PromotionsUnavailable(
                __('Card promotions are not available right now. Please try again in a few minutes.')
            );
        }

        $offer = $this->firstOffer($payload);
        $defaultMerchant = $payload['defaultMerchant'] ?? null;

        if ($offer === null && $cardBrand !== '') {
            $payload = $this->promotions->get($cardBrand);
            $offer = $this->firstOffer($payload);
            $defaultMerchant = $payload['defaultMerchant'] ?? $defaultMerchant;
        }

        $plan = $offer === null
            ? $this->defaultPlan($defaultMerchant, $installments)
            : $this->planFromOffer($offer, $installments);

        $this->assertMerchantMatches($plan, $merchantNumber, $bin, $cardBrand, $installments);

        return $plan;
    }

    /**
     * @param array $payload
     *
     * @return array|null
     */
    private function firstOffer(array $payload): ?array
    {
        $offers = $payload['promotions'] ?? [];

        if (!is_array($offers) || $offers === []) {
            return null;
        }

        $offer = reset($offers);

        if (!is_array($offer) || empty($offer['installments'])) {
            return null;
        }

        return $offer;
    }

    /**
     * Single installment, no surcharge, on the brand's default merchant.
     *
     * @param mixed $defaultMerchant
     * @param int $installments
     *
     * @return InstallmentPlan
     * @throws LocalizedException
     */
    private function defaultPlan($defaultMerchant, int $installments): InstallmentPlan
    {
        if ($installments !== 1) {
            throw new LocalizedException(
                __('The selected installment plan is no longer available. Please choose it again.')
            );
        }

        return $this->planFactory->create([
            'installments' => 1,
            'rate' => self::NO_SURCHARGE_RATE,
            'merchantNumber' => $this->merchantNumberOf($defaultMerchant)
        ]);
    }

    /**
     * @param array $offer
     * @param int $installments
     *
     * @return InstallmentPlan
     * @throws LocalizedException
     */
    private function planFromOffer(array $offer, int $installments): InstallmentPlan
    {
        foreach ($offer['installments'] as $candidate) {
            if (!is_array($candidate) || (int) ($candidate['quantity'] ?? 0) !== $installments) {
                continue;
            }

            return $this->planFactory->create([
                'installments' => $installments,
                'rate' => $this->assertRate($candidate['rate'] ?? null, $installments),
                'merchantNumber' => $this->merchantNumberOf($offer['merchant'] ?? null)
            ]);
        }

        throw new LocalizedException(
            __('The selected installment plan is no longer available. Please choose it again.')
        );
    }

    /**
     * A rate above self::MAX_RATE overcharges, and a non-finite one multiplies the total
     * into nonsense: neither can be allowed to reach the gateway, whether they come from tampering
     * or from a compromised promotions response, so both are still rejected.
     *
     * @param mixed $value
     * @param int $installments
     *
     * @return float
     * @throws LocalizedException
     */
    private function assertRate($value, int $installments): float
    {
        $rate = is_numeric($value) ? (float) $value : 0.0;

        if (!is_finite($rate) || $rate > self::MAX_RATE) {
            $this->logger->critical(
                'Line Payment: refusing an out-of-range installment rate from the promotions service.',
                [
                    'installments' => $installments,
                    'rate' => is_scalar($value) ? (string) $value : gettype($value),
                    'ceiling' => self::MAX_RATE
                ]
            );

            throw new LocalizedException(
                __('We can\'t process this payment right now. Please try a different payment method.')
            );
        }

        if ($rate < self::NO_SURCHARGE_RATE) {
            return self::NO_SURCHARGE_RATE;
        }

        return $rate;
    }

    /**
     * @param mixed $merchant
     *
     * @return string
     * @throws LocalizedException
     */
    private function merchantNumberOf($merchant): string
    {
        $number = is_array($merchant) ? ($merchant['number'] ?? null) : null;

        if (!is_scalar($number) || (string) $number === '') {
            throw new LocalizedException(
                __('We can\'t process this payment right now. Please try a different payment method.')
            );
        }

        return (string) $number;
    }

    /**
     * The browser's merchant number is compared, never forwarded. A mismatch means the payload was
     * edited between the checkout rendering it and the order being placed.
     *
     * @param InstallmentPlan $plan
     * @param string|null $submitted
     * @param string $bin
     * @param string $cardBrand
     * @param int $installments
     *
     * @return void
     * @throws LocalizedException
     */
    private function assertMerchantMatches(
        InstallmentPlan $plan,
        ?string $submitted,
        string $bin,
        string $cardBrand,
        int $installments
    ): void {
        if ($submitted === null || $submitted === '' || $submitted === $plan->getMerchantNumber()) {
            return;
        }

        $this->logger->critical(
            'Line Payment: submitted merchant number does not match the resolved plan.',
            [
                'bin' => $bin,
                'card_brand' => $cardBrand,
                'installments' => $installments,
                'submitted_merchant' => $submitted,
                'resolved_merchant' => $plan->getMerchantNumber()
            ]
        );

        throw new LocalizedException(
            __('We can\'t process this payment right now. Please try a different payment method.')
        );
    }
}
