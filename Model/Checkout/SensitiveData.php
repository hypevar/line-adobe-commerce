<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Model\Checkout;

use Line\Payment\Api\Data\Checkout\SensitiveDataInterface;
use Line\Payment\Model\Security\CardFingerprint;

/**
 * Immutable holder for the cardholder data of the current request.
 */
class SensitiveData implements SensitiveDataInterface
{
    private string $pan;
    private string $cvv;
    private string $bin;
    private string $fingerprint;

    /**
     * @param CardFingerprint $fingerprint
     * @param string $pan
     * @param string $cvv
     */
    public function __construct(
        CardFingerprint $fingerprint,
        string $pan,
        string $cvv
    ) {
        $this->pan = (string) preg_replace('/\D/', '', $pan);
        $this->cvv = (string) preg_replace('/\D/', '', $cvv);
        $this->bin = substr($this->pan, 0, 6);
        $this->fingerprint = $fingerprint->of($this->pan);
    }

    /**
     * @inheritdoc
     */
    public function getPan(): string
    {
        return $this->pan;
    }

    /**
     * @inheritdoc
     */
    public function getCvv(): string
    {
        return $this->cvv;
    }

    /**
     * @inheritdoc
     */
    public function getBin(): string
    {
        return $this->bin;
    }

    /**
     * @inheritdoc
     */
    public function getFingerprint(): string
    {
        return $this->fingerprint;
    }

    /**
     * Keeps the PAN out of string interpolation.
     */
    public function __toString(): string
    {
        return '***';
    }

    /**
     * Keeps the PAN out of var_dump() and of exception traces.
     *
     * @return array<string, string>
     */
    public function __debugInfo(): array
    {
        return ['pan' => '***', 'cvv' => '***', 'bin' => $this->bin];
    }
}
