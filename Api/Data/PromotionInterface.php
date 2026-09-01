<?php
/**
 * Copyright © 2024 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Api\Data;

use Line\Payment\Api\Data\Promotion\BankInterface;
use Line\Payment\Api\Data\Promotion\InstallmentInterface;

interface PromotionInterface
{
    /**#@+
     * @access public
     * @var string
     */
    public const FIELD_ID = 'id';
    public const FIELD_ENABLED = 'enabled';
    public const FIELD_START_DATE = 'startDate';
    public const FIELD_END_DATE = 'endDate';
    public const FIELD_DAYS_OF_WEEK = 'daysOfWeek';
    public const FIELD_INSTALLMENTS = 'installments';
    public const FIELD_BANK = 'bank';
    /**#@-*/

    public function getId();
    public function setId();

    /**
     * @return bool
     */
    public function getEnabled();
    public function setEnabled(bool $value);

    public function getStartDate();
    public function setStartDate(string $value);

    public function getEndDate();
    public function setEndDate(string $value);

    /**
     * @return string[]
     */
    public function getDaysOfWeek();
    public function setDaysOfWeek(array $value);

    /**
     * @return InstallmentInterface[]
     */
    public function getInstallments(): array;
    public function setInstallments($value): array;

    /**
     * @return BankInterface;
     */
    public function getBank(): BankInterface;
    public function setBank($value);
}
