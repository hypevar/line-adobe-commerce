<?php
/**
 *
 */

declare(strict_types=1);

namespace Line\Payment\Gateway\Response;

use Line\Payment\Api\Response\AttributeInterface;
use Line\Payment\Api\Response\HandlerInterface;
use Line\Payment\Gateway\DataReader;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Transaction;
use Psr\Log\LoggerInterface;

/**
 *
* [2024-03-04T12:29:40.469987+00:00] main.DEBUG: Connector Debug request {"method":"POST","http-uri":"/creditcard/anulacion/fb9abdaf5041e94b81b4484b443aaebf","body":[]} []
* [2024-03-04T12:29:46.720997+00:00] main.DEBUG: Connector Debug response {"method":"POST","url":"https://ccs.4fsoluciones.com.ar/linerestapitest/api/v1/creditcard/anulacion/fb9abdaf5041e94b81b4484b443aaebf","response":{"Identificador":"0001459931","IdentificadorCliente":"fb9abdaf5041e94b81b4484b443aaebf","IdentificadorClienteOriginal":"fb9abdaf5041e94b81b4484b443aaebf","Estado":"ANULADA","CodigoError":0,"Mensaje":"AUTORIZADA","MensajeFormato":"AUTORIZADA","NumeroTarjeta":"448779******0026","NumeroCuenta":"TEST","ModoIngreso":"WEB","VTEResult":"----","CodigoEstado":"approved","Detalle":[{"IdentificadorCliente":"fb9abdaf5041e94b81b4484b443aaebf-A01","IdentificadorClienteOriginal":"fb9abdaf5041e94b81b4484b443aaebf","NumeroTarjeta":null,"NumeroCuenta":null,"Fecha":"2024-03-04T09:29:46.4472933-03:00","Terminal":"60000003","Lote":969,"Cupon":3313,"PlanCuotas":"0","Cuotas":3,"CodigoAutorizacion":"443237","Estado":"AUTORIZADA","TipoOperacion":"ANULACION","CodigoError":0,"CodigoEstado":"approved"}],"AntiFraude":null,"Token":null,"FechaExpiracion":null,"Marca":null,"IdentificadorSoftDescriptor":null}} []
 *
 */

class RefundHandler implements HandlerInterface
{
    /**
     * @var DataReader
     */
    protected $reader;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param DataReader $reader
     * @param LoggerInterface $logger
     */
    public function __construct(
        DataReader $reader,
        LoggerInterface $logger
    ) {
        $this->reader = $reader;
        $this->logger = $logger;
    }

    /**
     * Whether parent transaction should be closed
     *
     * @param Payment $payment
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function shouldCloseParentTransaction(Payment $payment): bool
    {
        return !(bool)$payment->getCreditmemo()->getInvoice()->canRefund();
    }

    /**
     * @param array $handlingSubject
     * @param array $response
     */
    public function handle(array $handlingSubject, array $response)
    {
        $object = $this->reader->readPayment($handlingSubject);

        /** @var Payment $payment */
        $payment = $object->getPayment();

        if (!$payment instanceof Payment) {
            return;
        }

        $transaction = $this->reader->readTransaction($response);

        $this->addTransactionDetails($payment, $transaction);
    }

    /**
     * @param Payment $payment
     * @param $data
     */
    public function addTransactionDetails(Payment $payment, $data)
    {
        $raw = [];
        $raw[AttributeInterface::FIELD_IDENTIFIER] = $data->getIdentifier();
        $raw[AttributeInterface::FIELD_CUSTOMER_IDENTIFIER] = $data->getCustomerIdentifier();
        $raw[AttributeInterface::FIELD_MESSAGE] = $data->getMessage();
        $raw[AttributeInterface::FIELD_FORMATED_MESSAGE] = $data->getFormattedMessage();
        $raw[AttributeInterface::FIELD_STATUS] = $data->getStatus();
        $raw[AttributeInterface::FIELD_ERROR_CODE] = $data->getErrorCode();
        $raw[AttributeInterface::FIELD_STATUS_CODE] = $data->getStatusCode();

        // set all details into the right
        $payment->setTransactionAdditionalInfo(
            Transaction::RAW_DETAILS,
            $raw
        );
    }
}
