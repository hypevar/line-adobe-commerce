<?php
/**
 *
 */

declare(strict_types=1);

namespace Line\Payment\Model;

use Line\Payment\Api\Data\Checkout\PaymentAttributeInterface;
use Magento\Payment\Model\InfoInterface;

/**
 * Supplies the per-order reference the gateway knows as `IdentificadorCliente`.
 *
 * It used to be a fast unkeyed digest of storeId, increment id and the buyer's email. All three
 * inputs are guessable and the digest is cheap, so anyone who knew a customer's email could
 * reconstruct the reference used to look their transaction up. It is now a random 128 bit value,
 * rendered as 32 hex characters so the wire format is unchanged.
 *
 * The value is generated once and kept on the payment, so a retry of the same order re-uses it.
 */
class GetTransactionIdentifierAction
{
    /**
     * Bytes of entropy. 16 bytes render as the same 32 hex characters the md5 digest produced.
     */
    private const IDENTIFIER_BYTES = 16;

    /**
     * @param InfoInterface $payment
     *
     * @return string
     */
    public function generate(InfoInterface $payment): string
    {
        $existing = $payment->getAdditionalInformation(
            PaymentAttributeInterface::PAYMENT_TRANSACTION_IDENTIFIER
        );

        if (is_string($existing) && $existing !== '') {
            return $existing;
        }

        $identifier = bin2hex(random_bytes(self::IDENTIFIER_BYTES));

        $payment->setAdditionalInformation(
            PaymentAttributeInterface::PAYMENT_TRANSACTION_IDENTIFIER,
            $identifier
        );

        return $identifier;
    }
}
