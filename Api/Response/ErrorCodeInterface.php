<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Api\Response;

/**
 *
 * @link https://line.net.ar/documentacion/#anexo-2
 */
interface ErrorCodeInterface
{
    /**#@+
     * @access public
     * @var int
     */
    /**
     * Array key to be used to hold the error label
     */
    public const ARRAY_KEY_LABEL = 'label';
    /**
     * Array key to be used to hold the error description
     */
    public const ARRAY_KEY_DESCRIPTION = 'description';
    /**#@-*/

    /**#@+
     * @access public
     * @var int
     */
    public const CODE_AUTHORIZED = 0;
    public const CODE_ERROR = -1;
    /**#@-*/

    /**#@+
     * @access public
     * @var int
     */
    public const CODE_APPROVED_2 = 00;
    public const CODE_NEEDS_AUTHORIZATION = 01;
    public const CODE_NEEDS_AUTHORIZATION_2 = 02;
    public const CODE_INVALID_BUSINESS = 03;
    public const CODE_HOLD_CARD = 04;
    public const CODE_DENIED = 05;
    public const CODE_HOLD_AND_NOTIFY = 07;
    public const CODE_APPROVED_3 = 11;
    public const CODE_INVALID_TRANSACTION = 12;
    public const CODE_INVALID_AMOUNT = 13;
    public const CODE_INVALID_CARD = 14;
    public const CODE_ORIGINAL_NOT_FOUND = 25;
    public const CODE_SERVICE_UNAVAILABLE = 28;
    public const CODE_FORMAT_ERROR = 30;
    public const CODE_DCC_APPLIES = 31;
    public const CODE_ING_PIN_EXCEEDS = 38;
    public const CODE_HOLD_CARD_2 = 43;
    public const CODE_INSTALLMENTS_NOT_SUPPORTED = 45;
    public const CODE_CARD_EXPIRED = 46;
    public const CODE_PIN_IS_REQUIRED = 47;
    public const CODE_MAX_INSTALLMENTS_EXCEEDED = 48;
    public const CODE_ERROR_EXPIRATION_DATE = 49;
    public const CODE_INSUFFICIENT_FUNDS = 51;
    public const CODE_UNEXISTING_ACCOUNT = 53;
    public const CODE_CARD_EXPIRED_2 = 54;
    public const CODE_INCORRECT_PIN = 55;
    public const CODE_CARD_NOT_ACTIVATED = 56;
    public const CODE_TRANSACTION_NOT_ALLOWED = 57;
    public const CODE_INVALID_SERVICE = 58;
    public const CODE_LIMIT_EXCEEDED = 61;
    public const CODE_CARD_LIMIT_EXCEEDED = 65;
    public const CODE_CALL_VENDOR = 76;
    public const CODE_ERROR_INSTALLMENTS_PLAN = 77;
    public const CODE_APPROVED_4 = 85;
    public const CODE_INVALID_TERMINAL = 89;
    public const CODE_EMITTER_OFFLINE = 91;
    public const CODE_DUPLICATED_SEC_NUMBER = 94;
    public const CODE_RE_TRANSMITTING = 95;
    public const CODE_SYSTEM_ERROR = 96;
    public const CODE_CHECK_TICKET_REJECTION = 98;
    /**#@-*/

    /**
     * @access public
     * @var array
     */
    public const ERROR_CODE_LIST = [
        self::CODE_APPROVED_2 => [
            self::ARRAY_KEY_LABEL => "APROBADA",
            self::ARRAY_KEY_DESCRIPTION => "Operación aprobada"
        ],
        self::CODE_NEEDS_AUTHORIZATION => [
            self::ARRAY_KEY_LABEL => "PEDIR AUTORIZACION",
            self::ARRAY_KEY_DESCRIPTION => "Solicitar autorización telefónica, en caso de ser aprobada, cargar el código obtenido y dejar la operación en OFFLINE."
        ],
        self::CODE_NEEDS_AUTHORIZATION_2 => [
            self::ARRAY_KEY_LABEL => "PEDIR AUTORIZACION",
            self::ARRAY_KEY_DESCRIPTION => "Solicitar autorización telefónica, en caso de ser aprobada, cargar el código obtenido y dejar la operación en OFFLINE."
        ],
        self::CODE_INVALID_BUSINESS => [
            self::ARRAY_KEY_LABEL => "COMERCIO INVALIDO",
            self::ARRAY_KEY_DESCRIPTION => "Verificar parámetros del sistema, código de comercio mal cargado"
        ],
        self::CODE_HOLD_CARD => [
            self::ARRAY_KEY_LABEL => "CAPTURAR TARJETA",
            self::ARRAY_KEY_DESCRIPTION => "Denegada, capturar la tarjeta."
        ],
        self::CODE_DENIED => [
            self::ARRAY_KEY_LABEL => "DENEGADA",
            self::ARRAY_KEY_DESCRIPTION => "Denegada."
        ],
        self::CODE_HOLD_AND_NOTIFY => [
            self::ARRAY_KEY_LABEL => "RETENGA Y LLAME",
            self::ARRAY_KEY_DESCRIPTION => "Denegada, llamar al Centro de Autorizaciones."
        ],
        self::CODE_APPROVED_3 => [
            self::ARRAY_KEY_LABEL => "APROBADA",
            self::ARRAY_KEY_DESCRIPTION => "Operación aprobada, emitir cupón (cargo o ticket)."
        ],
        self::CODE_INVALID_TRANSACTION => [
            self::ARRAY_KEY_LABEL => "TRANSAC. INVALIDA",
            self::ARRAY_KEY_DESCRIPTION => "Verificar el sistema, transacción no reconocida en el sistema."
        ],
        self::CODE_INVALID_AMOUNT => [
            self::ARRAY_KEY_LABEL => "MONTO INVALIDO",
            self::ARRAY_KEY_DESCRIPTION => "Verificar el sistema, error en el formato del campo importe."
        ],
        self::CODE_INVALID_CARD => [
            self::ARRAY_KEY_LABEL => "TARJETA INVALIDA",
            self::ARRAY_KEY_DESCRIPTION => "Denegada, tarjeta no corresponde."
        ],
        self::CODE_ORIGINAL_NOT_FOUND => [
            self::ARRAY_KEY_LABEL => "NO EXISTE ORIGINAL",
            self::ARRAY_KEY_DESCRIPTION => "Denegada, registro no encontrado en el archivo de transacciones."
        ],
        self::CODE_SERVICE_UNAVAILABLE => [
            self::ARRAY_KEY_LABEL => "SERVICIO NO DISPONIBLE",
            self::ARRAY_KEY_DESCRIPTION => "Momentáneamente el servicio no está disponible. Se debe reintentar en unos segundos."
        ],
        self::CODE_FORMAT_ERROR => [
            self::ARRAY_KEY_LABEL => "ERROR EN FORMATO",
            self::ARRAY_KEY_DESCRIPTION => "Verificar el sistema, error en el formato del mensaje."
        ],
        self::CODE_DCC_APPLIES => [
            self::ARRAY_KEY_LABEL => "APLICA DCC",
            self::ARRAY_KEY_DESCRIPTION => "Devuelve al POS información de tipo de cambio y moneda extranjera."
        ],
        self::CODE_ING_PIN_EXCEEDS => [
            self::ARRAY_KEY_LABEL => "EXCEDE ING.DE PIN",
            self::ARRAY_KEY_DESCRIPTION => "Denegada, excede cantidad de reintentos de PIN permitidos."
        ],
        self::CODE_HOLD_CARD_2 => [
            self::ARRAY_KEY_LABEL => "RETENER TARJETA",
            self::ARRAY_KEY_DESCRIPTION => "Denegada, retener tarjeta."
        ],
        self::CODE_INSTALLMENTS_NOT_SUPPORTED => [
            self::ARRAY_KEY_LABEL => "NO OPERA EN CUOTAS",
            self::ARRAY_KEY_DESCRIPTION => "Denegada, tarjeta inhibida para operar en cuotas."
        ],
        self::CODE_CARD_EXPIRED => [
            self::ARRAY_KEY_LABEL => "TARJETA NO VIGENTE",
            self::ARRAY_KEY_DESCRIPTION => "Denegada, la tarjeta no está vigente aún."
        ],
        self::CODE_PIN_IS_REQUIRED => [
            self::ARRAY_KEY_LABEL => "PIN REQUERIDO",
            self::ARRAY_KEY_DESCRIPTION => "Denegada, tarjeta requiere ingreso de PIN."
        ],
        self::CODE_MAX_INSTALLMENTS_EXCEEDED => [
            self::ARRAY_KEY_LABEL => "EXCEDE MAX. CUOTAS",
            self::ARRAY_KEY_DESCRIPTION => "Denegada, excede cantidad máxima de cuotas permitida."
        ],
        self::CODE_ERROR_EXPIRATION_DATE => [
            self::ARRAY_KEY_LABEL => "ERROR FECHA VENCIM.",
            self::ARRAY_KEY_DESCRIPTION => "Verificar el sistema, error en formato de fecha de expiración (vto)"
        ],
        self::CODE_INSUFFICIENT_FUNDS => [
            self::ARRAY_KEY_LABEL => "FONDOS INSUFICIENTES",
            self::ARRAY_KEY_DESCRIPTION => "Denegada, no posee fondos suficientes."
        ],
        self::CODE_UNEXISTING_ACCOUNT => [
            self::ARRAY_KEY_LABEL => "CUENTA INEXISTENTE",
            self::ARRAY_KEY_DESCRIPTION => "Denegada, no existe cuenta asociada."
        ],
        self::CODE_CARD_EXPIRED_2 => [
            self::ARRAY_KEY_LABEL => "TARJETA VENCIDA",
            self::ARRAY_KEY_DESCRIPTION => "Denegada, tarjeta expirada."
        ],
        self::CODE_INCORRECT_PIN => [
            self::ARRAY_KEY_LABEL => "PIN INCORRECTO",
            self::ARRAY_KEY_DESCRIPTION => "Denegada, el código de identificación personal es incorrecto."
        ],
        self::CODE_CARD_NOT_ACTIVATED => [
            self::ARRAY_KEY_LABEL => "TARJ. NO HABILITADA",
            self::ARRAY_KEY_DESCRIPTION => "Denegada, emisor no habilitado en el sistema."
        ],
        self::CODE_TRANSACTION_NOT_ALLOWED => [
            self::ARRAY_KEY_LABEL => "TRANS. NO PERMITIDA",
            self::ARRAY_KEY_DESCRIPTION => "Verificar el sistema, transacción no permitida a dicha tarjeta."
        ],
        self::CODE_INVALID_SERVICE => [
            self::ARRAY_KEY_LABEL => "SERVICIO INVALIDO",
            self::ARRAY_KEY_DESCRIPTION => "Verificar el sistema, transacción no permitida a dicha terminal."
        ],
        self::CODE_LIMIT_EXCEEDED => [
            self::ARRAY_KEY_LABEL => "EXCEDE LIMITE",
            self::ARRAY_KEY_DESCRIPTION => "Denegada, excede el límite remanente de la tarjeta."
        ],
        self::CODE_CARD_LIMIT_EXCEEDED => [
            self::ARRAY_KEY_LABEL => "EXCEDE LIM. TARJETA",
            self::ARRAY_KEY_DESCRIPTION => "Denegada, excede el límite remanente de la tarjeta."
        ],
        self::CODE_CALL_VENDOR => [
            self::ARRAY_KEY_LABEL => "LLAMAR AL EMISOR",
            self::ARRAY_KEY_DESCRIPTION => "Solicitar autorización telefónica, en caso de ser aprobada, cargar el código obtenido y dejar la operación en OFFLINE."
        ],
        self::CODE_ERROR_INSTALLMENTS_PLAN => [
            self::ARRAY_KEY_LABEL => "ERROR PLAN / CUOTAS",
            self::ARRAY_KEY_DESCRIPTION => "Denegada, cantidad de cuotas inválida para el plan seleccionado."
        ],
        self::CODE_APPROVED_4 => [
            self::ARRAY_KEY_LABEL => "APROBADA",
            self::ARRAY_KEY_DESCRIPTION => "Operación aprobada, emitir cupón (cargo o ticket)."
        ],
        self::CODE_INVALID_TERMINAL => [
            self::ARRAY_KEY_LABEL => "TERMINAL INVALIDA",
            self::ARRAY_KEY_DESCRIPTION => "Denegada, número de terminal no habilitado por el Emisor."
        ],
        self::CODE_EMITTER_OFFLINE => [
            self::ARRAY_KEY_LABEL => "EMISOR FUERA LINEA",
            self::ARRAY_KEY_DESCRIPTION => "Solicitar autorización telefónica, en caso de ser aprobada, cargar el código obtenido y dejar la operación en OFFLINE."
        ],
        self::CODE_DUPLICATED_SEC_NUMBER => [
            self::ARRAY_KEY_LABEL => "NRO. SEC. DUPLICAD",
            self::ARRAY_KEY_DESCRIPTION => "Denegada. Error en el mensaje. Envíe nuevamente la transacción incrementando en uno el system trace de la misma."
        ],
        self::CODE_RE_TRANSMITTING => [
            self::ARRAY_KEY_LABEL => "RE-TRANSMITIENDO",
            self::ARRAY_KEY_DESCRIPTION => "Diferencias en la conciliación del cierre, envíe Batch Upload."
        ],
        self::CODE_SYSTEM_ERROR => [
            self::ARRAY_KEY_LABEL => "ERROR EN SISTEMA",
            self::ARRAY_KEY_DESCRIPTION => "Mal funcionamiento del sistema. Solicitar autorización telefónica."
        ],
        self::CODE_CHECK_TICKET_REJECTION => [
            self::ARRAY_KEY_LABEL => "VER RECHAZO EN TICKET",
            self::ARRAY_KEY_DESCRIPTION => "Deben imprimir la información suministrada en el campo ISO 63."
        ]
    ];
}
