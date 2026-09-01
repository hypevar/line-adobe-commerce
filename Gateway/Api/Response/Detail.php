<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Gateway\Api\Response;

use Line\Payment\Api\Response\AttributeDetailInterface;
use Magento\Framework\DataObject;

/**
 *
 * @api
 * @since 0.1.0
 */
class Detail extends DataObject implements AttributeDetailInterface
{
    /**
     * @inheritDoc
     */
    public function getCustomerIdentifier(): string
    {
        return $this->getData(self::FIELD_CUSTOMER_IDENTIFIER);
    }

    /**
     * @inheritDoc
     */
    public function setCustomerIdentifier(string $value): self
    {
        $this->setData(self::FIELD_CUSTOMER_IDENTIFIER, $value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getOriginalCustomerIdentifier(): string
    {
        return $this->getData(self::FIELD_ORIGINAL_CUSTOMER_IDENTIFIER);
    }

    /**
     * @inheritDoc
     */
    public function setOriginalCustomerIdentifier(string $value): self
    {
        $this->setData(self::FIELD_ORIGINAL_CUSTOMER_IDENTIFIER, $value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCreditCardNumber(): string
    {
        return $this->getData(self::FIELD_CREDIT_CARD_NUMBER);
    }

    /**
     * @inheritDoc
     */
    public function setCreditCardNumber(string $value): self
    {
        $this->setData(self::FIELD_CREDIT_CARD_NUMBER, $value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getAccountNumber(): string
    {
        return $this->getData(self::FIELD_ACCOUNT_NUMBER);
    }

    /**
     * @inheritDoc
     */
    public function setAccountNumber(string $value): self
    {
        $this->setData(self::FIELD_ACCOUNT_NUMBER, $value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getDate(): string
    {
        return $this->getData(self::FIELD_DATE);
    }

    /**
     * @inheritDoc
     */
    public function setDate(string $value): self
    {
        $this->setData(self::FIELD_DATE, $value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getTerminal(): string
    {
        return $this->getData(self::FIELD_TERMINAL);
    }

    /**
     * @inheritDoc
     */
    public function setTerminal(string $value): self
    {
        $this->setData(self::FIELD_TERMINAL, $value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getBatch(): int
    {
        return $this->getData(self::FIELD_BATCH);
    }

    /**
     * @inheritDoc
     */
    public function setBatch(int $value): self
    {
        $this->setData(self::FIELD_BATCH, $value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCoupon(): int
    {
        return $this->getData(self::FIELD_COUPON);
    }

    /**
     * @inheritDoc
     */
    public function setCoupon(int $value): self
    {
        $this->setData(self::FIELD_COUPON, $value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getInstallmentsPlan(): int
    {
        return $this->getData(self::FIELD_INSTALLMENTS_PLAN);
    }

    /**
     * @inheritDoc
     */
    public function setInstallmentsPlan(string $value): self
    {
        $this->setData(self::FIELD_INSTALLMENTS_PLAN, $value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getInstallments(): int
    {
        return $this->getData(self::FIELD_INSTALLMENTS);
    }

    /**
     * @inheritDoc
     */
    public function setInstallments(int $value): self
    {
        $this->setData(self::FIELD_INSTALLMENTS, $value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getAuthorizationCode(): string
    {
        return $this->getData(self::FIELD_AUTH_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setAuthorizationCode(string $value): self
    {
        $this->setData(self::FIELD_AUTH_CODE, $value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getStatus(): string
    {
        return $this->getData(self::FIELD_STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setStatus(string $value): self
    {
        $this->setData(self::FIELD_STATUS, $value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getOperationType(): string
    {
        return $this->getData(self::FIELD_OPERATION_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setOperationType(string $value): self
    {
        $this->setData(self::FIELD_OPERATION_TYPE, $value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getErrorCode(): int
    {
        return $this->getData(self::FIELD_ERROR_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setErrorCode(int $value): self
    {
        $this->setData(self::FIELD_ERROR_CODE, $value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getStatusCode(): string
    {
        return $this->getData(self::FIELD_ERROR_STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setStatusCode(string $value): self
    {
        $this->setData(self::FIELD_ERROR_STATUS, $value);
        return $this;
    }
}
