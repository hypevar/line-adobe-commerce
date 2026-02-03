<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Gateway\Http\Client;

use Line\Payment\Api\Data\ErrorCodesInterface;
use Line\Payment\Gateway\DataReader;
use Line\Payment\Model\Adapter;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;
use Psr\Log\LoggerInterface;

abstract class AbstractTransaction implements ClientInterface
{
    protected Adapter $adapter;
    protected DataReader $reader;
    protected LoggerInterface $logger;
    protected Logger $customLogger;

    /**
     * @param LoggerInterface $logger
     * @param Logger $customLogger
     * @param Adapter $adapter
     * @param DataReader $reader
     */
    public function __construct(
        LoggerInterface $logger,
        Logger $customLogger,
        Adapter $adapter,
        DataReader $reader
    ) {
        $this->logger = $logger;
        $this->customLogger = $customLogger;
        $this->adapter = $adapter;
        $this->reader = $reader;
    }

    /**
     * @inheritdoc
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $data = $transferObject->getBody();

        $log = ['request' => $data, 'client' => static::class];
        $response['object'] = [];

        try {
            $response['object'] = $this->process($data);

        } catch (\Exception $e) {
            $message = $e->getMessage();
            $this->logger->critical($message);

            $response['object'] = $e->getMessage();

            // We add the exception into the response array
            $response['exception'] = new ClientException(
                __($message),
                $e,
                ErrorCodesInterface::CODE_API_ERROR
            );
        } finally {
            // prevent logger depth exception by extracting response
            $log['response'] = is_object($response['object'])
                ? $this->reader->readTransaction((array) $response)
                : $response['object'];

            $this->customLogger->debug($log, null, true);
        }

        return $response;
    }

    /**
     * @param array $data
     */
    abstract protected function process(array $data);
}
