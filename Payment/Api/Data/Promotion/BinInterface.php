<?php
/**
 * Copyright © 2024 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Api\Data\Promotion;


interface BinInterface
{
    /**#@+
     * @access public
     * @var string
     */
    public const FIELD_ID = 'id';
    public const FIELD_NUMBER = 'number';
    public const FIELD_CARD_TYPE = 'cardType';
    /**#@-*/

    public function getId(): string;
    public function setId(string $value): self;

    public function getNumber(): string;
    public function setNumber(string $value): self;

    public function getCardType(): string;
    public function setCardType(string $value): self;
}
