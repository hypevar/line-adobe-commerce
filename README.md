# Line Payments para Adobe Commerce / Magento

Implementación del gateway de pago de Line Payments para Adobe Commerce y Magento.

## Módulo

- `line/module-payment` (`Line_Payment`), versión `1.0.0`: gateway de pago para checkout.

Declara compatibilidad con PHP 8.1, 8.2 y 8.3, y con los componentes Magento 2.4 correspondientes.

## Módulo relacionado

`line/module-verified-purchase` (`Line_VerifiedPurchase`), el flujo de verificación antifraude,
vive en un repositorio separado:
[line-verified-purchase-adobe-commerce](https://github.com/hypevar/line-verified-purchase-adobe-commerce).
Depende de este módulo; la dependencia es unidireccional (`Line_Payment` no lo referencia).
Los puntos de extensión que consume son `Line\Payment\Gateway\Request\IntegrationPool`,
un plugin sobre `Line\Payment\Gateway\Request\DetailsDataBuilder`, el evento
`line_payment_data_converter_before`, `Line\Payment\Model\GetTransactionIdentifierAction` y
`Line\Payment\Model\Checkout\SensitiveDataRegistry` — este último es la única fuente del PAN
desde 0.5.0, cuando se lo sacó de `additional_information`. Es el único de los cinco que no es
una interfaz de `Api\`: al refactorizarlo hay que contemplar el módulo de verificación.

## Documentación

Consultar la [guía de configuración](docs/configuracion.md) para los parámetros de gateway, promociones, cuotas, estados de orden, logs y recomendaciones operativas.

## Estado del proyecto

El módulo de este repositorio es una divergencia del último módulo oficial conocido. No constituye el módulo oficial y continúa en evolución.

---

## Magento Support
2.4.6-sp3

## Instalación

Antes de instalarlo hay que declarar el repositorio VCS en el `composer.json` raíz del proyecto Magento:

```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/hypevar/line-adobe-commerce.git"
    }
]
```

Equivalente por CLI, si se prefiere no editar el archivo a mano:

```bash
composer config repositories.line-payment vcs https://github.com/hypevar/line-adobe-commerce.git
```

Con el repositorio declarado:

```bash
composer require line/module-payment:^1.0
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento cache:flush
```

## Changelog
* 0.4.1:
    - integrates second API call so, when no promotions are pulled by BIN, all Promotions from that particular `cardBrand` without an association to a bank are rendered.
    - fix: when filters by installment is saved without values, causes a `null` return.
* 0.4.0:
    - replaces Promotions API call: now retrieves promotions based on the CC Bin
    - feature: Filter Installments by Order Grand Total
    - fix: sorting for installment options
* 0.3.6:
    - fix: start/end date filtering: now correctly filters by these two params, plus the day of the week
    - fix: correctly sort installments by `quantity` attribute, within the Installment dropdown form element in the Checkout
* 0.3.5:
    - fix for installments: now dropdown picks up value correctly
    - improve: when changing the card number, now month and year gets cleared out
* 0.3.4:
    - improve card matching while evaluating against regexp
    - promotion validation selection against entity-code
    - improve when evaluation gets fired: now all digits are required before evaluate CC number
    - add: new config option for enable/disable showing the price of each installment

* 0.3.3: remove NumeroComercio as configurable field. now retrieves that value from Promotion

* 0.3.2: bugfix and rendering improvements
    - bugfix for sandbox/production configuration methods.
    - improve emitters empty response detection, to avoid checkout form use.

* 0.3.1: add check to avoid rendering checkout configuration if module is disabled

* 0.2.0: add `integration_pool` class so external modules are able to add new fields into the authorization request

* 0.1.0: initial module

## Sandbox Configuration
Sandbox Api Key:
```
eb0872bebd934e1c94979f7476c68242
```
Please, refer to official documentation for an updated value, in case it's not valid anymore: [Propiedades del Header HTTP](https://line.net.ar/documentacion/#prop-header-http)

## Plain response
## Success
```js
{
    "payment": {},
    "amount": 28.82,
    "response": {
        "object": {
            "Identificador": "0001458279",
            "IdentificadorCliente": "9a3e8390-6321-41f2-bdfb-56783fd70753",
            "IdentificadorClienteOriginal": null,
            "Estado": "AUTORIZADA",
            "CodigoError": 0,
            "Mensaje": "AUTORIZADA",
            "MensajeFormato": "Transacci\u00f3n autorizada",
            "NumeroTarjeta": "450799******1026",
            "NumeroCuenta": "TEST",
            "ModoIngreso": "WEB",
            "VTEResult": "----",
            "CodigoEstado": "approved",
            "Detalle": [
                {
                    "IdentificadorCliente": "9a3e8390-6321-41f2-bdfb-56783fd70753",
                    "IdentificadorClienteOriginal": "",
                    "NumeroTarjeta": null,
                    "NumeroCuenta": null,
                    "Fecha": "2023-12-19T16: 03: 23.0870759-03: 00",
                    "Terminal": "72501520",
                    "Lote": 719,
                    "Cupon": 6720,
                    "PlanCuotas": "0",
                    "Cuotas": 3,
                    "CodigoAutorizacion": "075960",
                    "Estado": "AUTORIZADA",
                    "TipoOperacion": "COMPRA",
                    "CodigoError": 0,
                    "CodigoEstado": "approved"
                }
            ],
            "AntiFraude": null,
            "Token": "",
            "FechaExpiracion": "****",
            "Marca": "VISA",
            "IdentificadorSoftDescriptor": null
        }
    }
}```

## Error
```js
{
    "payment": {},
    "amount": 28.82,
    "response": {
        "object": {
            "Identificador": "0001458282",
            "IdentificadorCliente": "9a3e8390-6321-41f2-bdfb-56783fd70753",
            "IdentificadorClienteOriginal": null,
            "Estado": "ERROR",
            "CodigoError": -1,
            "Mensaje": "Ya existe una TXAutorizacion con identificador 9a3e8390-6321-41f2-bdfb-56783fd70753",
            "MensajeFormato": "Error al intentar autorizar. Ya existe una TXAutorizacion con identificador 9a3e8390-6321-41f2-bdfb-56783fd70753",
            "NumeroTarjeta": "450799******1026",
            "NumeroCuenta": "",
            "ModoIngreso": "",
            "VTEResult": "----",
            "CodigoEstado": "card_rejected_duplicated_payment",
            "Detalle": [],
            "AntiFraude": null,
            "Token": "",
            "FechaExpiracion": "****",
            "Marca": "",
            "IdentificadorSoftDescriptor": null
        }
    }
}
```

```js
 {
      "payment":{},
      "amount":38.82,
      "response":{
          "object":"{\"Message\":\"Authorization has been denied for this request.\"}"
      }
 }
```


### Events exposed
* `line_payment_data_converter_before`
* `line_payment_request_builder_details_attribute`
