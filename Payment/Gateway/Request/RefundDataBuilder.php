<?php
/**
 *
 */

declare(strict_types=1);

namespace Line\Payment\Gateway\Request;

use InvalidArgumentException;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Model\Order\Payment;
use Line\Payment\Api\Request\RefundInterface;
use Line\Payment\Gateway\DataReader;
use Psr\Log\LoggerInterface;

class RefundDataBuilder implements BuilderInterface
{
    /**
     * @var DataReader
     */
    private $reader;

    /**
     * @var LoggerInterface
     */
    private $logger;

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
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $object = $this->reader->readPayment($buildSubject);

        /** @var Payment $payment */
        $payment = $object->getPayment();
        $amount = 0;

        try {
            $amount = $this->reader->readAmount($buildSubject);
        } catch (InvalidArgumentException $e) {
            $this->logger->error(
                'Line Payment: could not read the refund amount from the build subject.',
                ['error' => $e->getMessage()]
            );
        }

        return [
            RefundInterface::FIELD_IDENTIFIER => $payment->getCcTransId(),
            RefundInterface::FIELD_AMOUNT => $amount
        ];
    }
}
