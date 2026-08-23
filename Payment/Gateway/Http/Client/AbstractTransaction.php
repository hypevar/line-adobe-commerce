<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Gateway\Http\Client;

use Line\Payment\Api\Data\ConfigInterface;
use Line\Payment\Api\Data\ErrorCodesInterface;
use Line\Payment\Api\Request\AttributeInterface;
use Line\Payment\Gateway\DataReader;
use Line\Payment\Model\Adapter;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;
use Psr\Log\LoggerInterface;

abstract class AbstractTransaction implements ClientInterface
{
    /**
     * Values replaced with `****` before the payload reaches the log.
     *
     * The list is explicit rather than null: passing null to
     * Magento\Payment\Model\Method\Logger::debug() means "replace nothing", which wrote the PAN,
     * the CVV and the expiry to payment.log in the clear.
     *
     * @var string[]
     */
    private const DEBUG_REPLACE_KEYS = [
        AttributeInterface::FIELD_CREDIT_CARD_NUMBER,
        AttributeInterface::FIELD_CREDIT_CARD_CVV,
        AttributeInterface::FIELD_CREDIT_CARD_EXPIRATION_DATE,
        AttributeInterface::FIELD_CARDHOLDER_FULLNAME,
        AttributeInterface::FIELD_CARDHOLDER_DOCUMENT_NUMBER,
        AttributeInterface::FIELD_CARDHOLDER_EMAIL,
        AttributeInterface::FIELD_CUSTOMER_IP,
        AttributeInterface::FIELD_TRACK_I,
        AttributeInterface::FIELD_TRACK_II
    ];

    protected Adapter $adapter;
    protected DataReader $reader;
    protected LoggerInterface $logger;
    protected Logger $customLogger;
    protected ConfigInterface $config;

    /**
     * @param LoggerInterface $logger
     * @param Logger $customLogger
     * @param Adapter $adapter
     * @param DataReader $reader
     * @param ConfigInterface $config
     */
    public function __construct(
        LoggerInterface $logger,
        Logger $customLogger,
        Adapter $adapter,
        DataReader $reader,
        ConfigInterface $config
    ) {
        $this->logger = $logger;
        $this->customLogger = $customLogger;
        $this->adapter = $adapter;
        $this->reader = $reader;
        $this->config = $config;
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

            if ($this->config->isDebugEnabled()) {
                $this->customLogger->debug($log, self::DEBUG_REPLACE_KEYS, true);
            }
        }

        return $response;
    }

    /**
     * @param array $data
     */
    abstract protected function process(array $data);
}
