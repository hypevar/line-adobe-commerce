<?php
/**
 * Copyright © 2024 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Api\Data\Promotion;


interface BankInterface
{
    /**#@+
     * @access public
     * @var string
     */
    public const FIELD_ID = 'id';
    public const FIELD_NAME = 'name';
    public const FIELD_IMAGE = 'image';
    public const FIELD_BINES = 'bines';
    /**#@-*/

    public function getId(): string;
    public function setId($value): self;

    public function getName(): string;
    public function setName($value): self;

    /**
     * @return string|null
     */
    public function getImage();
    public function setImage($value): self;

    /**
     * @return BinInterface[]
     */
    public function getBines(): array;
    public function setBines($value): self;
}
