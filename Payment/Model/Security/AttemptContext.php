<?php
/**
 * Copyright © 2025 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Model\Security;

/**
 * The set of counter keys one authorization attempt belongs to.
 *
 * No value here is cardholder data: the card is represented by a keyed fingerprint, the email by a
 * hash, and the BIN is the issuer prefix.
 */
class AttemptContext
{
    /**#@+
     * @access public
     * @var string
     */
    public const DIMENSION_CARD = 'card';
    public const DIMENSION_BIN = 'bin';
    public const DIMENSION_QUOTE = 'quote';
    public const DIMENSION_EMAIL = 'email';
    public const DIMENSION_CUSTOMER = 'customer';
    public const DIMENSION_IP = 'ip';

    /**
     * Store-wide counter. Never blocks on its own; it only tightens the other thresholds.
     */
    public const DIMENSION_STORE = 'store';
    /**#@-*/

    /**
     * @var array<string, string>
     */
    private array $keys;

    private int $storeId;

    private ?int $quoteId;

    /**
     * @param array $keys dimension => value, omitting dimensions that do not apply
     * @param int $storeId
     * @param int|null $quoteId
     */
    public function __construct(
        array $keys,
        int $storeId,
        ?int $quoteId = null
    ) {
        $this->keys = $keys;
        $this->storeId = $storeId;
        $this->quoteId = $quoteId;
    }

    /**
     * @return array<string, string>
     */
    public function getKeys(): array
    {
        return $this->keys;
    }

    /**
     * @return int
     */
    public function getStoreId(): int
    {
        return $this->storeId;
    }

    /**
     * @return int|null
     */
    public function getQuoteId(): ?int
    {
        return $this->quoteId;
    }
}
