<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Gateway\Api;

use Line\Payment\Api\ResponseInterface;
use Line\Payment\Api\Response\AttributeInterface;
use Line\Payment\Api\Response\AttributeDetailInterface;
use Magento\Framework\DataObject;

/**
 *
 * @api
 * @since 0.1.0
 */
class Response extends DataObject implements ResponseInterface
{
    /**
     * @inheritDoc
     */
    public function getIdentifier(): string
    {
        return $this->getData(AttributeInterface::FIELD_IDENTIFIER);
    }

    /**
     * @inheritDoc
     */
    public function setIdentifier(string $value): self
    {
        $this->setData(AttributeInterface::FIELD_IDENTIFIER, $value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCustomerIdentifier(): string
    {
        return $this->getData(AttributeInterface::FIELD_CUSTOMER_IDENTIFIER);
    }

    /**
     * @inheritDoc
     */
    public function setCustomerIdentifier(string $value): self
    {
        $this->setData(AttributeInterface::FIELD_CUSTOMER_IDENTIFIER, $value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getOriginalCustomerIdentifier(): ?string
    {
        return $this->getData(AttributeInterface::FIELD_ORIGINAL_CUSTOMER_IDENTIFIER);
    }

    /**
     * @inheritDoc
     */
    public function setOriginalCustomerIdentifier(string $value): self
    {
        $this->setData(AttributeInterface::FIELD_ORIGINAL_CUSTOMER_IDENTIFIER, $value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getStatus(): string
    {
        return $this->getData(AttributeInterface::FIELD_STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setStatus(string $value): self
    {
        $this->setData(AttributeInterface::FIELD_STATUS, $value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getErrorCode(): int
    {
        return $this->getData(AttributeInterface::FIELD_ERROR_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setErrorCode(int $value): self
    {
        $this->setData(AttributeInterface::FIELD_ERROR_CODE, $value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getMessage(): string
    {
        return $this->getData(AttributeInterface::FIELD_MESSAGE);
    }

    /**
     * @inheritDoc
     */
    public function setMessage(string $value): self
    {
        $this->setData(AttributeInterface::FIELD_MESSAGE, $value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getFormattedMessage(): string
    {
        return $this->getData(AttributeInterface::FIELD_FORMATED_MESSAGE);
    }

    /**
     * @inheritDoc
     */
    public function setFormattedMessage(string $value): self
    {
        $this->setData(AttributeInterface::FIELD_FORMATED_MESSAGE, $value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCreditCardNumber(): ?string
    {
        return $this->getData(AttributeInterface::FIELD_CREDIT_CARD_NUMBER);
    }

    /**
     * @inheritDoc
     */
    public function setCreditCardNumber(string $value): self
    {
        $this->setData(AttributeInterface::FIELD_CREDIT_CARD_NUMBER, $value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getAccountNumber(): ?string
    {
        return $this->getData(AttributeInterface::FIELD_ACCOUNT_NUMBER);
    }

    /**
     * @inheritDoc
     */
    public function setAccountNumber(string $value): self
    {
        $this->setData(AttributeInterface::FIELD_ACCOUNT_NUMBER, $value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getEnteringMode(): string
    {
        return $this->getData(AttributeInterface::FIELD_ENTERING_MODE);
    }

    /**
     * @inheritDoc
     */
    public function setEnteringMode(string $value): self
    {
        $this->setData(AttributeInterface::FIELD_ENTERING_MODE, $value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getVTEResult(): string
    {
        return $this->getData(AttributeInterface::FIELD_VTE_RESULT);
    }

    /**
     * @inheritDoc
     */
    public function setVTEResult(string $value): self
    {
        $this->setData(AttributeInterface::FIELD_VTE_RESULT, $value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getStatusCode(): string
    {
        return $this->getData(AttributeInterface::FIELD_STATUS_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setStatusCode(string $value): self
    {
        $this->setData(AttributeInterface::FIELD_STATUS_CODE, $value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getAntiFraud(): string
    {
        return $this->getData(AttributeInterface::FIELD_PREVENT_FRAUD);
    }

    /**
     * @inheritDoc
     */
    public function setAntiFraud(string $value): self
    {
        $this->setData(AttributeInterface::FIELD_PREVENT_FRAUD, $value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getToken(): string
    {
        return $this->getData(AttributeInterface::FIELD_TOKEN);
    }

    /**
     * @inheritDoc
     */
    public function setToken(string $value): self
    {
        $this->setData(AttributeInterface::FIELD_TOKEN, $value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getExpirationDate(): string
    {
        return $this->getData(AttributeInterface::FIELD_EXPIRATION_DATE);
    }

    /**
     * @inheritDoc
     */
    public function setExpirationDate(string $value): self
    {
        $this->setData(AttributeInterface::FIELD_EXPIRATION_DATE, $value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCreditCardBrand(): ?string
    {
        return $this->getData(AttributeInterface::FIELD_CREDIT_CARD_BRAND);
    }

    /**
     * @inheritDoc
     */
    public function setCreditCardBrand(string $value): self
    {
        $this->setData(AttributeInterface::FIELD_CREDIT_CARD_BRAND, $value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getSoftDescriptorIdentifier(): string
    {
        return $this->getData(AttributeInterface::FIELD_SOFT_DESCRIPTOR_IDENTIFIER);
    }

    /**
     * @inheritDoc
     */
    public function setSoftDescriptorIdentifier(string $value): self
    {
        $this->setData(AttributeInterface::FIELD_SOFT_DESCRIPTOR_IDENTIFIER, $value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getDetail(): AttributeDetailInterface|null
    {
        return $this->getData(AttributeInterface::FIELD_DETAIL);
    }

    /**
     * @inheritDoc
     */
    public function setDetail(AttributeDetailInterface $data): self
    {
        $this->setData(AttributeInterface::FIELD_DETAIL, $data);
        return $this;
    }
}
