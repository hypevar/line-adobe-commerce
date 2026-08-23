<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Api\Data\Checkout;

/**
 * Cardholder data for the current request.
 *
 * Instances of this interface hold the PAN and the CVV in memory only. They must never be
 * written to `additional_information`, to any entity, to the session or to a log.
 *
 * @see \Line\Payment\Model\Checkout\SensitiveDataRegistry
 */
interface SensitiveDataInterface
{
    /**
     * Primary Account Number, digits only.
     */
    public function getPan(): string;

    /**
     * Card verification value, digits only.
     */
    public function getCvv(): string;

    /**
     * First six digits of the PAN. Not cardholder data.
     */
    public function getBin(): string;

    /**
     * Keyed, non-reversible fingerprint of the PAN. Safe to store and to log.
     */
    public function getFingerprint(): string;
}
