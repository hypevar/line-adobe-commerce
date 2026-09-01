<?php
/**
 * Copyright © 2024 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Api\Response;

/**
 *
 * @api
 * @since 0.1.0
 */
interface RefundInterface
{
    /**
     * {
     *     "method":"POST",
     *     "url":"https://ccs.4fsoluciones.com.ar/linerestapitest/api/v1/creditcard/anulacion/fb9abdaf5041e94b81b4484b443aaebf",
     *     "response":{
     *         "Identificador":"0001459931",
     *         "IdentificadorCliente":"fb9abdaf5041e94b81b4484b443aaebf",
     *         "IdentificadorClienteOriginal":"fb9abdaf5041e94b81b4484b443aaebf",
     *         "Estado":"ANULADA",
     *         "CodigoError":0,
     *         "Mensaje":"AUTORIZADA",
     *         "MensajeFormato":"AUTORIZADA",
     *         "NumeroTarjeta":"448779******0026",
     *         "NumeroCuenta":"TEST",
     *         "ModoIngreso":"WEB",
     *         "VTEResult":"----",
     *         "CodigoEstado":"approved",
     *         "Detalle":[{
     *             "IdentificadorCliente":"fb9abdaf5041e94b81b4484b443aaebf-A01",
     *             "IdentificadorClienteOriginal":"fb9abdaf5041e94b81b4484b443aaebf",
     *             "NumeroTarjeta":null,
     *             "NumeroCuenta":null,
     *             "Fecha":"2024-03-04T09:29:46.4472933-03:00",
     *             "Terminal":"60000003",
     *             "Lote":969,
     *             "Cupon":3313,
     *             "PlanCuotas":"0",
     *             "Cuotas":3,
     *             "CodigoAutorizacion":"443237",
     *             "Estado":"AUTORIZADA",
     *             "TipoOperacion":"ANULACION",
     *             "CodigoError":0,
     *             "CodigoEstado":"approved"
     *         }],
     *         "AntiFraude":null,
     *         "Token":null,
     *         "FechaExpiracion":null,
     *         "Marca":null,
     *         "IdentificadorSoftDescriptor":null
     *     }
     * } []
     */
}
