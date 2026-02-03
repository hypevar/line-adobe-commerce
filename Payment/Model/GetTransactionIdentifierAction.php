<?php
/**
 *
 */

declare(strict_types=1);

namespace Line\Payment\Model;

use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 *
 */
class GetTransactionIdentifierAction
{
    /** @var string */
    private const HASH_CHAR_SEPARATOR = '|';

    /**
     * Generates a unique identifier for a transaction
     * based on the Buyer's email and the Order data
     *
     * @return string
     */
    public function generate(OrderInterface|OrderAdapterInterface $order): string
    {
        $store = $order->getStoreId();
        $email = $order->getBillingAddress()->getEmail();

        if ($order instanceof OrderInterface) {
            $incrementId = $order->getIncrementId();
        } else {
            $incrementId = $order->getOrderIncrementId();
        }

        // final value to be hashed
        $value = $store
            . self::HASH_CHAR_SEPARATOR . $incrementId
            . self::HASH_CHAR_SEPARATOR . $email;

        return md5($value);
    }

    /**
     * @param string $identifier
     *
     * @return array
     */
    public function extract(string $identifier): array
    {
        return explode(self::HASH_CHAR_SEPARATOR, md5($identifier));
    }
}
