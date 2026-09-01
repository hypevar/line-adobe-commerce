<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Model\Checkout;

use Line\Payment\Api\Data\Checkout\SensitiveDataInterface;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;

/**
 * Carries the cardholder data from the checkout observer to the gateway request builders.
 *
 * Both run inside the same PHP request: PaymentInformationManagement::savePaymentInformationAndPlaceOrder()
 * calls paymentMethodManagement->set() (-> assignData -> the observer) and cartManagement->placeOrder()
 * (-> the builders) one after the other.
 */
class SensitiveDataRegistry implements ResetAfterRequestInterface
{
    private ?SensitiveDataInterface $data = null;

    /**
     * @param SensitiveDataInterface $data
     */
    public function set(SensitiveDataInterface $data): void
    {
        $this->data = $data;
    }

    /**
     * @return SensitiveDataInterface|null
     */
    public function get(): ?SensitiveDataInterface
    {
        return $this->data;
    }

    /**
     * @return bool
     */
    public function has(): bool
    {
        return $this->data !== null;
    }

    /**
     * @return void
     */
    public function clear(): void
    {
        $this->data = null;
    }

    /**
     * Under an application server (RoadRunner, FrankenPHP) the object survives the request.
     * A PAN must not.
     *
     * @inheritdoc
     */
    public function _resetState(): void
    {
        $this->clear();
    }
}
