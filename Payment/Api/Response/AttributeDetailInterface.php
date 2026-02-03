<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Api\Response;

/**
 * Representation on Magento side for the Details field in Gateway's response
 *
 * Original object from Gateway can be found in `GatewayAttribute\DetailInterface`
 * @see \Line\Payment\Api\Response\GatewayAttribute\DetailInterface
 */
interface AttributeDetailInterface
{
    /**
     *      "Detalle": [
     *         {
     *             "IdentificadorCliente": "xxxxxxxxx",
     *             "IdentificadorClienteOriginal": "",
     *             "NumeroTarjeta": null,
     *             "NumeroCuenta": null,
     *             "Fecha": "YYYY-MM-DDT11:11:11.7207613-03:00",
     *             "Terminal": "11100111",
     *             "Lote": 101,
     *             "Cupon": 1011,
     *             "PlanCuotas": "0",
     *             "Cuotas": 1,
     *             "CodigoAutorizacion": "110011",
     *             "Estado": "AUTORIZADA",
     *             "TipoOperacion": "COMPRA",
     *             "CodigoError": 0,
     *             "CodigoEstado": "approved"
     *         }
     *     ]
     */

    /**#@+
     * @access public
     * @var string
     */
    public const FIELD_CUSTOMER_IDENTIFIER = 'customer_identifier';
    public const FIELD_ORIGINAL_CUSTOMER_IDENTIFIER = 'original_customer_identifier';
    public const FIELD_CREDIT_CARD_NUMBER = 'credit_card_number';
    public const FIELD_ACCOUNT_NUMBER = 'account_number';
    public const FIELD_DATE = 'date';
    public const FIELD_TERMINAL = 'terminal';
    public const FIELD_BATCH = 'batch';
    public const FIELD_COUPON = 'coupon';
    public const FIELD_INSTALLMENTS_PLAN = 'installments_plan';
    public const FIELD_INSTALLMENTS = 'installments';
    public const FIELD_AUTH_CODE = 'authorization_code';
    public const FIELD_STATUS = 'status';
    public const FIELD_OPERATION_TYPE = 'operation_type';
    public const FIELD_ERROR_CODE = 'error_code';
    public const FIELD_ERROR_STATUS = 'status_code';
    /**#@+ */

    public function getCustomerIdentifier(): string;
    public function setCustomerIdentifier(string $value): self;

    public function getOriginalCustomerIdentifier(): string;
    public function setOriginalCustomerIdentifier(string $value): self;

    public function getCreditCardNumber(): string;
    public function setCreditCardNumber(string $value): self;

    public function getAccountNumber(): string;
    public function setAccountNumber(string $value): self;

    public function getDate(): string;
    public function setDate(string $value): self;

    public function getTerminal(): string;
    public function setTerminal(string $value): self;

    public function getBatch(): int;
    public function setBatch(int $value): self;

    public function getCoupon(): int;
    public function setCoupon(int $value): self;

    public function getInstallmentsPlan(): int;
    public function setInstallmentsPlan(string $value): self;

    public function getInstallments(): int;
    public function setInstallments(int $value): self;

    public function getAuthorizationCode(): string;
    public function setAuthorizationCode(string $value): self;

    public function getStatus(): string;
    public function setStatus(string $value): self;

    public function getOperationType(): string;
    public function setOperationType(string $value): self;

    public function getErrorCode(): int;
    public function setErrorCode(int $value): self;

    public function getStatusCode(): string;
    public function setStatusCode(string $value): self;
}
