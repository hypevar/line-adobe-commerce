<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Api\Response\GatewayAttribute;

use Line\Payment\Api\Response\AttributeDetailInterface;

/**
 * Holds all subnodes from Gateway response under `Detalle` attribute
 */
interface DetailInterface
{
    /**#@+
     * @access public
     * @var string
     */
    public const FIELD_CUSTOMER_IDENTIFIER = 'IdentificadorCliente';
    public const FIELD_ORIGINAL_CUSTOMER_IDENTIFIER = 'IdentificadorClienteOriginal';
    public const FIELD_CREDIT_CARD_NUMBER = 'NumeroTarjeta';
    public const FIELD_ACCOUNT_NUMBER = 'NumeroCuenta';
    public const FIELD_DATE = 'Fecha';
    public const FIELD_TERMINAL = 'Terminal';
    public const FIELD_BATCH = 'Lote';
    public const FIELD_COUPON = 'Cupon';
    public const FIELD_INSTALLMENTS_PLAN = 'PlanCuotas';
    public const FIELD_INSTALLMENTS = 'Cuotas';
    public const FIELD_AUTH_CODE = 'CodigoAutorizacion';
    public const FIELD_STATUS = 'Estado';
    public const FIELD_OPERATION_TYPE = 'TipoOperacion';
    public const FIELD_ERROR_CODE = 'CodigoError';
    public const FIELD_ERROR_STATUS = 'CodigoEstado';
    /**#@+ */

    public const ATTRIBUTE_MATCHING = [
        self::FIELD_CUSTOMER_IDENTIFIER => AttributeDetailInterface::FIELD_CUSTOMER_IDENTIFIER,
        self::FIELD_ORIGINAL_CUSTOMER_IDENTIFIER => AttributeDetailInterface::FIELD_ORIGINAL_CUSTOMER_IDENTIFIER,
        self::FIELD_CREDIT_CARD_NUMBER => AttributeDetailInterface::FIELD_CREDIT_CARD_NUMBER,
        self::FIELD_ACCOUNT_NUMBER => AttributeDetailInterface::FIELD_ACCOUNT_NUMBER,
        self::FIELD_DATE => AttributeDetailInterface::FIELD_DATE,
        self::FIELD_TERMINAL => AttributeDetailInterface::FIELD_TERMINAL,
        self::FIELD_BATCH => AttributeDetailInterface::FIELD_BATCH,
        self::FIELD_COUPON => AttributeDetailInterface::FIELD_COUPON,
        self::FIELD_INSTALLMENTS_PLAN => AttributeDetailInterface::FIELD_INSTALLMENTS_PLAN,
        self::FIELD_INSTALLMENTS => AttributeDetailInterface::FIELD_INSTALLMENTS,
        self::FIELD_AUTH_CODE => AttributeDetailInterface::FIELD_AUTH_CODE,
        self::FIELD_STATUS => AttributeDetailInterface::FIELD_STATUS,
        self::FIELD_OPERATION_TYPE => AttributeDetailInterface::FIELD_OPERATION_TYPE,
        self::FIELD_ERROR_CODE => AttributeDetailInterface::FIELD_ERROR_CODE,
        self::FIELD_ERROR_STATUS => AttributeDetailInterface::FIELD_ERROR_STATUS,
    ];

    /**
     * @return string
     */
    public function getIdentificadorCliente(): string;

    /**
     * @param string $value
     *
     * @return self
     */
    public function setIdentificadorCliente(string $value): self;

    /**
     * @return string
     */
    public function getIdentificadorClienteOriginal(): string;

    /**
     * @param string $value
     *
     * @return self
     */
    public function setIdentificadorClienteOriginal(string $value): self;

    /**
     * @return string
     */
    public function getNumeroTarjeta(): string;

    /**
     * @param string $value
     *
     * @return self
     */
    public function setNumeroTarjeta(string $value): self;

    /**
     * @return string
     */
    public function getNumeroCuenta(): string;

    /**
     * @param string $value
     *
     * @return self
     */
    public function setNumeroCuenta(string $value): self;

    /**
     * @return string
     */
    public function getFecha(): string;

    /**
     * @param string $value
     *
     * @return self
     */
    public function setFecha(string $value): self;

    /**
     * @return string
     */
    public function getTerminal(): string;

    /**
     * @param string $value
     *
     * @return self
     */
    public function setTerminal(string $value): self;

    /**
     * @return string
     */
    public function getLote(): int;

    /**
     * @param string $value
     *
     * @return self
     */
    public function setLote(int $value): self;

    /**
     * @return string
     */
    public function getCupon(): int;

    /**
     * @param string $value
     *
     * @return self
     */
    public function setCupon(int $value): self;

    /**
     * @return string
     */
    public function getPlanCuotas(): string;

    /**
     * @param string $value
     *
     * @return self
     */
    public function setPlanCuotas(string $value): self;

    /**
     * @return string
     */
    public function getCuotas(): int;

    /**
     * @param string $value
     *
     * @return self
     */
    public function setCuotas(int $value): self;

    /**
     * @return string
     */
    public function getCodigoAutorizacion(): string;

    /**
     * @param string $value
     *
     * @return self
     */
    public function setCodigoAutorizacion(string $value): self;

    /**
     * @return string
     */
    public function getEstado(): string;

    /**
     * @param string $value
     *
     * @return self
     */
    public function setEstado(string $value): self;

    /**
     * @return string
     */
    public function getTipoOperacion(): string;

    /**
     * @param string $value
     *
     * @return string
     */
    public function setTipoOperacion(string $value): self;

    /**
     * @return int
     */
    public function getCodigoError(): int;

    /**
     * @param int $value
     *
     * @return self
     */
    public function setCodigoError(int $value): self;

    /**
     * @return string
     */
    public function getCodigoEstado(): string;

    /**
     * @param string $value
     *
     * @return string
     */
    public function setCodigoEstado(string $value): self;
}
