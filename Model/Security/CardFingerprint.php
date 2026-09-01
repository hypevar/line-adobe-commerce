<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Model\Security;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Exception\LocalizedException;

/**
 * Produces a keyed, non-reversible fingerprint of a PAN.
 *
 * The key is mandatory. A bare digest of a PAN is enumerable: an attacker holding the table only
 * has to hash the ~10^8 Luhn-valid numbers under a BIN to recover every card in it. Keying the
 * hash with the instance crypt key removes that.
 */
class CardFingerprint
{
    private DeploymentConfig $deploymentConfig;

    private ?string $key = null;

    /**
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(DeploymentConfig $deploymentConfig)
    {
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * @param string $pan
     * @return string
     * @throws LocalizedException
     */
    public function of(string $pan): string
    {
        return $this->keyedHash($pan);
    }

    /**
     * Same keyed HMAC as {@see of()}, for other values that must not be enumerable from an
     * unkeyed digest (e.g. an email address) but are not themselves a PAN.
     *
     * @param string $value
     * @return string
     * @throws LocalizedException
     */
    public function keyedHash(string $value): string
    {
        return hash_hmac('sha256', $value, $this->getKey());
    }

    /**
     * Current crypt key. Magento appends rotated keys line by line; the last one is in use.
     *
     * @return string
     * @throws LocalizedException
     */
    private function getKey(): string
    {
        if ($this->key !== null) {
            return $this->key;
        }

        $keys = (string) $this->deploymentConfig->get('crypt/key');
        $lines = array_filter(explode(PHP_EOL, $keys), 'strlen');

        if ($lines === []) {
            throw new LocalizedException(__('Line Payment: the instance encryption key is missing.'));
        }

        return $this->key = (string) end($lines);
    }
}
