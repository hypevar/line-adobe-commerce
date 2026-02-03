<?php
/**
 * Copyright © 2024 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Api\Data\Promotion;


interface InstallmentInterface
{
    /**#@+
     * @access public
     * @var string
     */
    public const FIELD_ID = 'id';
    public const FIELD_NAME = 'name';
    public const FIELD_DISPLAY_NAME = 'displayName';
    public const FIELD_QUANTITY = 'quantity';
    public const FIELD_RATE = 'rate';
    /**#@-*/

    public function getIdentifier(): string;
    public function getName(): string;
    public function getDisplayName();
    public function getQuantity(): int;
    public function getRate(): int;
}
