<?php
/**
 *
 */

declare(strict_types=1);

namespace Line\Payment\Gateway\Validator;

use Line\Payment\Api\Response\StatusCodeInterface;
use Line\Payment\Api\Response\StatusInterface;
use Line\Payment\Api\Response\ValidatorInterface;
use Line\Payment\Api\ResponseInterface;
use Line\Payment\Gateway\DataReader;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Psr\Log\LoggerInterface;

class RefundValidator extends AbstractValidator implements ValidatorInterface
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
     * @param ResultInterfaceFactory $resultFactory
     * @param DataReader $reader
     * @param LoggerInterface $logger
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        DataReader $reader,
        LoggerInterface $logger
    ) {
        $this->reader = $reader;
        $this->logger = $logger;

        parent::__construct($resultFactory);
    }

    public function validate(array $subject): ResultInterface
    {
        $object = $this->reader->readResponse($subject);
        /** @var ResponseInterface $response */
        $response = $this->reader->readResponseObject($object);

        $isValid = false;
        $errorMessages = [];
        $errorCodes = [];

        $status = $response->getStatus();
        $statusCode = $response->getStatusCode();

        $isValid = $status === StatusInterface::STATUS_ANNULLED
            && $statusCode === StatusCodeInterface::STATUS_CODE_APPROVED
                ? true
                : false;

        if (!$isValid) {
            $errorMessages[] = $response->getFormattedMessage();
            $errorCodes[] = $statusCode;
            $this->logger->error($response->getCustomerIdentifier());
            $this->logger->error('Could not be refunded');
            $this->logger->error($response->getFormattedMessage());
        }

        return $this->createResult($isValid, $errorMessages, $errorCodes);
    }
}
