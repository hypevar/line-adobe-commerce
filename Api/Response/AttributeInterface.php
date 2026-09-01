<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Api\Response;

interface AttributeInterface
{
    /**
     * {
     *     "Identificador": "0001458096",
     *     "IdentificadorCliente": "xxdasdasdasd",
     *     "IdentificadorClienteOriginal": null,
     *     "Estado": "AUTORIZADA",
     *     "CodigoError": 0,
     *     "Mensaje": "AUTORIZADA",
     *     "MensajeFormato": "Transacción autorizada",
     *     "NumeroTarjeta": "450799******1026",
     *     "NumeroCuenta": "TEST",
     *     "ModoIngreso": "WEB",
     *     "VTEResult": "----",
     *     "CodigoEstado": "approved",
     *     "IdentificadorSoftDescriptor": null
     *     "Detalle": [{}]
     * }
     */
    public const FIELD_IDENTIFIER = 'identifier';
    public const FIELD_CUSTOMER_IDENTIFIER = 'customer_identifier';
    public const FIELD_ORIGINAL_CUSTOMER_IDENTIFIER = 'original_customer_identifier';
    public const FIELD_STATUS = 'status';
    public const FIELD_ERROR_CODE = 'error_code';
    public const FIELD_MESSAGE = 'message';
    public const FIELD_FORMATED_MESSAGE = 'formatted_message';
    public const FIELD_CREDIT_CARD_NUMBER = 'credit_card_number';
    public const FIELD_ACCOUNT_NUMBER = 'account_number';
    public const FIELD_ENTERING_MODE = 'entering_mode';
    public const FIELD_VTE_RESULT = 'vte_result';
    public const FIELD_STATUS_CODE = 'status_code';
    public const FIELD_PREVENT_FRAUD = 'anti_fraud';
    public const FIELD_TOKEN = 'token';
    public const FIELD_EXPIRATION_DATE = 'expiration_date';
    public const FIELD_CREDIT_CARD_BRAND = 'credit_card_brand';
    public const FIELD_SOFT_DESCRIPTOR_IDENTIFIER = 'soft_descriptor_identifier';
    public const FIELD_DETAIL = 'detail';
}
