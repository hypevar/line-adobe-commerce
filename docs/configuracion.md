# Configuración de Line Payments para Magento

Esta guía describe la configuración operativa de `line/module-payment` (`Line_Payment`),
el gateway de pago utilizado en el checkout.

El módulo complementario `line/module-verified-purchase` (`Line_VerifiedPurchase`) vive en
[line-verified-purchase-adobe-commerce](https://github.com/hypevar/line-verified-purchase-adobe-commerce)
y se documenta allí.

## Acceso a la configuración

Luego de instalar y habilitar el módulo, ir a:

`Stores > Configuration > Sales > Payment Methods`

## Line Payments

### Parámetros generales

| Campo | Configuración |
| --- | --- |
| **Status** | Habilita o deshabilita el medio de pago en el checkout. |
| **Title** | Nombre visible para el cliente y en la exportación de órdenes. |
| **Mode** | Usar `Sandbox` para pruebas y `Production` para transacciones reales. |

### Credenciales, endpoints y logs

- Cargar la **Production API Key** provista por Line Payments.
- Mantener la URL de producción predeterminada salvo indicación del equipo técnico: `https://ccs.4fsoluciones.com.ar/linerestapi/api`.
- Mantener **Developer Debug** deshabilitado en producción. Al habilitarlo, los eventos se registran en `var/log/line-payment.log`.

### Medios de pago y checkout

- **Available Credit Cards** define las tarjetas disponibles. Se recomienda mantener todas habilitadas salvo una restricción comercial específica.
- **Document Types** define los tipos de documento visibles en checkout. Se recomienda mantener todos habilitados.
- **Display Installments Price** muestra el importe de cada cuota (`X CUOTAS DE $XXX`) cuando está habilitado; de lo contrario, muestra solo la cantidad de cuotas.
- **Sort Order** define la posición del medio de pago en el checkout.

### Estados de orden

**New Order Status** es el estado asignado al procesar el pago.

- Sin Verified Purchase: usar un estado equivalente a *Pago aprobado*.
- Con Verified Purchase: usar un estado intermedio equivalente a *Esperando verificación*.

### Promociones y cuotas

| Campo | Valor o recomendación |
| --- | --- |
| **Terminal System** | `TERM001`, o el valor provisto por Line Payments. |
| **Promotions Marketplace** | `magento`. |
| **Promotions Marketplace Id** | Identificador del sitio provisto por Line Payments. |
| **Promotions Production URL** | `https://api.dashboard.line.net.ar`; no modificar sin indicación técnica. |
| **Installments Filter** | Habilita el filtrado de cuotas por monto. |

En **Installments Filter Configuration**, definir una regla por cada esquema de cuotas: monto mínimo y todas las cuotas disponibles para ese monto. Por ejemplo, para habilitar 1, 3 y 6 cuotas a partir de $200.000, configurar `1, 3, 6`. Incluir también los valores de planes especiales que correspondan, por ejemplo `11`, `13` o `16`.

### Configuración técnica y limpieza de órdenes

- Configurar **API Version** en `version 1`.
- Mantener **API SSL** habilitado y **API SSL Version** en `TLS 1.2`.
- **Clean Expired Orders** elimina órdenes expiradas según la configuración `sales/orders/delete_pending_after`.

## Recomendaciones operativas

- Usar `Production` únicamente en el entorno productivo.
- No cambiar endpoints ni parámetros técnicos sin indicación de Line Payments.
- Mantener el debug deshabilitado fuera de una investigación puntual.
- Verificar que los estados de Line Payments y Verified Purchase formen un flujo consistente.
- Probar las reglas de cuotas en checkout antes de habilitarlas en producción.

Sin Verified Purchase, un pago aprobado finaliza el flujo. Con Verified Purchase, la orden pasa primero por un estado de verificación. Una configuración incorrecta puede dejar órdenes aprobadas sin validación, bloqueadas o en estados inconsistentes.
