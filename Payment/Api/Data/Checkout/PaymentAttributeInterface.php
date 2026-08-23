<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Api\Data\Checkout;

/**
 * Field names to be used within the Additional Information data
 *
 * This interface exposes all the field names that comes from the Checkout Form
 * and the Payment Additional Information will hold.
 * It can be used to safely access the Additional Information data.
 *
 * @see Line_Payment::js/view/payment/method-renderer/line-payment.js
 * @see \Line\Payment\Gateway\Response\PaymentInformationHandler
 * @see \Line\Payment\Observer\DataAssignObserver
 */
interface PaymentAttributeInterface
{
    /**#@+
     * @access public
     * @var string
     */
    public const CREDIT_CARD_HOLDER_NAME = 'cardholder_name';
    public const CREDIT_CARD_DOC_NUMBER = 'cardholder_doc_number';
    public const CREDIT_CARD_DOC_TYPE = 'cardholder_doc_type';

    public const CREDIT_CARD_NUMBER = 'credit_card_number';
    /**
     * Holds credit card brand name
     */
    public const CREDIT_CARD_TYPE = 'credit_card_type';
    /**
     * Holds whether it's a credit or debit card
     */
    public const CREDIT_CARD_METHOD = 'credit_card_method';
    public const CREDIT_CARD_EXP_YEAR = 'credit_card_exp_year';
    public const CREDIT_CARD_EXP_MONTH = 'credit_card_exp_month';
    public const CREDIT_CARD_CVV = 'credit_card_cvv';

    public const PAYMENT_INSTALLMENTS = 'installments';

    /**
     * Merchant number from selected promotion (aka. Numero de Comercio)
     */
    public const PAYMENT_MERCHANT_NUMBER = 'merchant_number';

    /**
     * Rate coefficient from selected installment (e.g. 1.15 = 15% surcharge)
     *
     * Server-authoritative output only. The checkout observer does not read this field from the
     * request: it is written by \Line\Payment\Gateway\Request\DetailsDataBuilder with the rate
     * the server resolved, so that the admin view and downstream consumers see the coefficient the
     * gateway was actually charged with.
     *
     * @deprecated as an input. Anything reading this as a customer-supplied value is a defect.
     */
    public const PAYMENT_INSTALLMENT_RATE = 'installment_rate';

    /**
     * Random per-order reference sent to the Gateway as `IdentificadorCliente`
     *
     * @see \Line\Payment\Model\GetTransactionIdentifierAction
     */
    public const PAYMENT_TRANSACTION_IDENTIFIER = 'transaction_identifier';

    /**
     * Authorization Code from the Gateway response object
     */
    public const CREDIT_CARD_AUTORIZATION_CODE = 'card_authorization_code';
    /**#@-*/

}
