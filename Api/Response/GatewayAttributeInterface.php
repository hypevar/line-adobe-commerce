<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Api\Response;

use Line\Payment\Api\Response\GatewayAttribute\DetailInterface;

/**
 * Holds all response fields from Gateway's response
 */
interface GatewayAttributeInterface
{
    /**#@+
     * @access public
     * @var string
     */
    public const FIELD_IDENTIFIER = 'Identificador';
    public const FIELD_CUSTOMER_IDENTIFIER = 'IdentificadorCliente';
    public const FIELD_ORIGINAL_CUSTOMER_IDENTIFIER = 'IdentificadorClienteOriginal';
    public const FIELD_STATUS = 'Estado';
    public const FIELD_ERROR_CODE = 'CodigoError';
    public const FIELD_MESSAGE = 'Mensaje';
    public const FIELD_FORMATED_MESSAGE = 'MensajeFormato';
    public const FIELD_CREDIT_CARD_NUMBER = 'NumeroTarjeta';
    public const FIELD_ACCOUNT_NUMBER = 'NumeroCuenta';
    public const FIELD_ENTERING_MODE = 'ModoIngreso';
    public const FIELD_VTE_RESULT = 'VTEResult';
    public const FIELD_STATUS_CODE = 'CodigoEstado';
    public const FIELD_PREVENT_FRAUD = 'AntiFraude';
    public const FIELD_TOKEN = 'Token';
    public const FIELD_EXPIRATION_DATE = 'FechaExpiracion';
    public const FIELD_CREDIT_CARD_BRAND = 'Marca';
    public const FIELD_SOFT_DESCRIPTOR_IDENTIFIER = 'IdentificadorSoftDescriptor';
    public const FIELD_DETAIL = 'Detalle';
    /**#@+ */

    /** @var string[] List of fields that holds the `detail` response attribute */
    public const DETAILS_FIELD_LIST = [
        DetailInterface::FIELD_CUSTOMER_IDENTIFIER,
        DetailInterface::FIELD_ORIGINAL_CUSTOMER_IDENTIFIER,
        DetailInterface::FIELD_CREDIT_CARD_NUMBER,
        DetailInterface::FIELD_ACCOUNT_NUMBER,
        DetailInterface::FIELD_DATE,
        DetailInterface::FIELD_TERMINAL,
        DetailInterface::FIELD_BATCH,
        DetailInterface::FIELD_COUPON,
        DetailInterface::FIELD_INSTALLMENTS_PLAN,
        DetailInterface::FIELD_INSTALLMENTS,
        DetailInterface::FIELD_AUTH_CODE,
        DetailInterface::FIELD_STATUS,
        DetailInterface::FIELD_OPERATION_TYPE,
        DetailInterface::FIELD_ERROR_CODE,
        DetailInterface::FIELD_ERROR_STATUS
    ];

    /**
     * Matches Gateway's response attribute names with Adobe Commerce attributes
     *
     * Built to ease integrating object's data creation.
     * plus, manage the same language across all module
     *
     * Details node from response has been isolated into it's own DataObject
     * @see \Line\Payment\Api\Response\GatewayAttribute\DetailInterface
     *
     * @var array
     */
    public const ATTRIBUTE_MATCHING = [
        self::FIELD_IDENTIFIER => AttributeInterface::FIELD_IDENTIFIER,
        self::FIELD_CUSTOMER_IDENTIFIER => AttributeInterface::FIELD_CUSTOMER_IDENTIFIER,
        self::FIELD_ORIGINAL_CUSTOMER_IDENTIFIER => AttributeInterface::FIELD_ORIGINAL_CUSTOMER_IDENTIFIER,
        self::FIELD_STATUS => AttributeInterface::FIELD_STATUS,
        self::FIELD_ERROR_CODE => AttributeInterface::FIELD_ERROR_CODE,
        self::FIELD_MESSAGE => AttributeInterface::FIELD_MESSAGE,
        self::FIELD_FORMATED_MESSAGE => AttributeInterface::FIELD_FORMATED_MESSAGE,
        self::FIELD_CREDIT_CARD_NUMBER => AttributeInterface::FIELD_CREDIT_CARD_NUMBER,
        self::FIELD_ACCOUNT_NUMBER => AttributeInterface::FIELD_ACCOUNT_NUMBER,
        self::FIELD_ENTERING_MODE => AttributeInterface::FIELD_ENTERING_MODE,
        self::FIELD_VTE_RESULT => AttributeInterface::FIELD_VTE_RESULT,
        self::FIELD_STATUS_CODE => AttributeInterface::FIELD_STATUS_CODE,
        self::FIELD_PREVENT_FRAUD => AttributeInterface::FIELD_PREVENT_FRAUD,
        self::FIELD_TOKEN => AttributeInterface::FIELD_TOKEN,
        self::FIELD_EXPIRATION_DATE => AttributeInterface::FIELD_EXPIRATION_DATE,
        self::FIELD_CREDIT_CARD_BRAND => AttributeInterface::FIELD_CREDIT_CARD_BRAND,
        self::FIELD_SOFT_DESCRIPTOR_IDENTIFIER => AttributeInterface::FIELD_SOFT_DESCRIPTOR_IDENTIFIER,
        self::FIELD_DETAIL => AttributeInterface::FIELD_DETAIL,
    ];
}
