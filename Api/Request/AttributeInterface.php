<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Api\Request;

/**
 * Holds all request fields accepted by the Gateway
 *
 * @link https://line.net.ar/documentacion/#transacciones-ecommerce-presenciales
 */
interface AttributeInterface
{
    /**#@+
     * @access public
     * @var string
     */
    public const FIELD_CUSTOMER_IDENTIFIER = 'IdentificadorCliente';

    public const FIELD_CUSTOMER_IP = 'IPAddress';
    /**
     * @see Attribute\SalesChannelInterface
     */
    public const FIELD_SALES_CHANNEL = 'CanalVenta';
    public const FIELD_TERMINAL_SYSTEM = 'TerminalSistema';
    /**
     * @see Attribute\TerminalTypeInterface
     */
    public const FIELD_TERMINAL_TYPE = 'TerminalTipo';
    /**
     * @see Attribute\CardCodeInterface
     */
    public const FIELD_CREDIT_CARD_EMITTER_CODE = 'CodigoEmisor';
    /**
     * @see Attribute\EnteringModeInterface
     */
    public const FIELD_ENTERING_MODE = 'ModoIngreso';
    public const FIELD_CURRENCY = 'Moneda';
    public const FIELD_CREDIT_CARD_NUMBER = 'NumeroTarjeta';
    public const FIELD_CREDIT_CARD_EXPIRATION_DATE = 'FechaExpiracion';
    public const FIELD_TRACK_I = 'TrackI';
    public const FIELD_TRACK_II = 'TrackII';
    public const FIELD_CREDIT_CARD_CVV = 'CodigoSeguridad';
    /**
     * @see Attribute\CardTypeInterface
     */
    public const FIELD_CREDIT_CARD_TYPE = 'TarjetaTipo';
    public const FIELD_CARDHOLDER_DOCUMENT_TYPE = 'TipoDocumento';
    public const FIELD_CARDHOLDER_DOCUMENT_NUMBER = 'DocumentoTitular';
    public const FIELD_CARDHOLDER_FULLNAME = 'NombreTitular';
    public const FIELD_CARDHOLDER_EMAIL = 'EmailTitular';
    public const FIELD_REFERENCE = 'Referencia';

    public const FIELD_DETAIL = 'Detalle';
    public const FIELD_DETAIL_BUSINESS_NUMBER = 'NumeroComercio';
    public const FIELD_DETAIL_AMOUNT = 'Importe';
    public const FIELD_DETAIL_INSTALLMENTS = 'Cuotas';
    /**#@-*/
}
