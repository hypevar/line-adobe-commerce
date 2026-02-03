<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Api;

use Line\Payment\Api\Response\AttributeDetailInterface;

/**
 *
 */
interface ResponseInterface
{
    /**
     * @return string
     */
    public function getIdentifier(): string;

    /**
     * @param string $value
     *
     * @return self
     */
    public function setIdentifier(string $value): self;

    /**
     * @return string
     */
    public function getCustomerIdentifier(): string;

    /**
     * @param string $value
     *
     * @return self
     */
    public function setCustomerIdentifier(string $value): self;

    /**
     * @return string|null
     */
    public function getOriginalCustomerIdentifier(): ?string;

    /**
     * @param string $value
     *
     * @return self
     */
    public function setOriginalCustomerIdentifier(string $value): self;

    /**
     * @return string
     */
    public function getStatus(): string;

    /**
     * @param string $value
     *
     * @return self
     */
    public function setStatus(string $value): self;

    /**
     * @return int
     */
    public function getErrorCode(): int;

    /**
     * @param int $value
     *
     * @return self
     */
    public function setErrorCode(int $value): self;

    /**
     * @return string
     */
    public function getMessage(): string;

    /**
     * @param string $value
     *
     * @return self
     */
    public function setMessage(string $value): self;

    /**
     * @return string
     */
    public function getFormattedMessage(): string;

    /**
     * @param string $value
     *
     * @return self
     */
    public function setFormattedMessage(string $value): self;

    /**
     * @return string|null
     */
    public function getCreditCardNumber(): ?string;

    /**
     * @param string $value
     *
     * @return self
     */
    public function setCreditCardNumber(string $value): self;

    /**
     * @return string|null
     */
    public function getAccountNumber(): ?string;

    /**
     * @param string $value
     *
     * @return self
     */
    public function setAccountNumber(string $value): self;

    /**
     * @return string
     */
    public function getEnteringMode(): string;

    /**
     * @param string $value
     *
     * @return self
     */
    public function setEnteringMode(string $value): self;

    /**
     * @return string
     */
    public function getVTEResult(): string;

    /**
     * @param string $value
     *
     * @return self
     */
    public function setVTEResult(string $value): self;

    /**
     * @return string
     */
    public function getStatusCode(): string;

    /**
     * @param string $value
     *
     * @return self
     */
    public function setStatusCode(string $value): self;

    /**
     * @return string
     */
    public function getAntiFraud(): string;

    /**
     * @param string $value
     *
     * @return self
     */
    public function setAntiFraud(string $value): self;

    /**
     * @return string
     */
    public function getToken(): string;

    /**
     * @param string $value
     *
     * @return self
     */
    public function setToken(string $value): self;

    /**
     * @return string
     */
    public function getExpirationDate(): string;

    /**
     * @param string $value
     *
     * @return self
     */
    public function setExpirationDate(string $value): self;

    /**
     * @return string|null
     */
    public function getCreditCardBrand(): ?string;

    /**
     * @param string $value
     *
     * @return self
     */
    public function setCreditCardBrand(string $value): self;

    /**
     * @return string
     */
    public function getSoftDescriptorIdentifier(): string;

    /**
     * @param string $value
     *
     * @return self
     */
    public function setSoftDescriptorIdentifier(string $value): self;

    /**
     * @return AttributeDetailInterface|null
     */
    public function getDetail(): AttributeDetailInterface|null;

    /**
     * @param AttributeDetailInterface $data
     *
     * @return self
     */
    public function setDetail(AttributeDetailInterface $data): self;
}
