<?php
/**
 * Copyright © 2025 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Model\Security;

use Line\Payment\Api\Data\ConfigInterface;
use Line\Payment\Gateway\DataReader;
use Line\Payment\Model\Checkout\SensitiveDataRegistry;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Psr\Log\LoggerInterface;

/**
 * Derives the counter keys of the authorization currently being attempted.
 */
class AttemptContextBuilder
{
    private DataReader $reader;
    private SensitiveDataRegistry $registry;
    private ConfigInterface $config;
    private RemoteAddress $remoteAddress;
    private AttemptContextFactory $contextFactory;
    private CardFingerprint $fingerprint;
    private LoggerInterface $logger;

    /**
     * @param DataReader $reader
     * @param SensitiveDataRegistry $registry
     * @param ConfigInterface $config
     * @param RemoteAddress $remoteAddress
     * @param AttemptContextFactory $contextFactory
     * @param CardFingerprint $fingerprint
     * @param LoggerInterface $logger
     */
    public function __construct(
        DataReader $reader,
        SensitiveDataRegistry $registry,
        ConfigInterface $config,
        RemoteAddress $remoteAddress,
        AttemptContextFactory $contextFactory,
        CardFingerprint $fingerprint,
        LoggerInterface $logger
    ) {
        $this->reader = $reader;
        $this->registry = $registry;
        $this->config = $config;
        $this->remoteAddress = $remoteAddress;
        $this->contextFactory = $contextFactory;
        $this->fingerprint = $fingerprint;
        $this->logger = $logger;
    }

    /**
     * @param array $subject a gateway build or validation subject
     *
     * @return AttemptContext|null null when the subject carries no payment
     */
    public function build(array $subject): ?AttemptContext
    {
        try {
            $payment = $this->reader->readPayment($subject);
        } catch (\InvalidArgumentException $exception) {
            return null;
        }

        /** @var OrderAdapterInterface $order */
        $order = $payment->getOrder();
        $keys = [];

        $card = $this->registry->get();

        if ($card !== null) {
            $keys[AttemptContext::DIMENSION_CARD] = $card->getFingerprint();

            if ($card->getBin() !== '') {
                $keys[AttemptContext::DIMENSION_BIN] = $card->getBin();
            }
        }

        $quoteId = $this->resolveQuoteId($payment->getPayment());

        if ($quoteId !== null) {
            $keys[AttemptContext::DIMENSION_QUOTE] = (string) $quoteId;
        }

        $email = $this->resolveEmail($order);

        if ($email !== null) {
            $hashed = $this->hashEmail($email);

            if ($hashed !== null) {
                $keys[AttemptContext::DIMENSION_EMAIL] = $hashed;
            }
        }

        $customerId = $order->getCustomerId();

        if ($customerId) {
            $keys[AttemptContext::DIMENSION_CUSTOMER] = (string) $customerId;
        }

        $storeId = (int) $order->getStoreId();

        if ($this->config->isAntifraudIpEnabled()) {
            $ip = $this->remoteAddress->getRemoteAddress();

            if (is_string($ip) && $ip !== '') {
                $keys[AttemptContext::DIMENSION_IP] = $ip;
            }
        }

        $keys[AttemptContext::DIMENSION_STORE] = (string) $storeId;

        return $this->contextFactory->create([
            'keys' => $keys,
            'storeId' => $storeId,
            'quoteId' => $quoteId
        ]);
    }

    /**
     * Keyed so the counter table is not a confirmable set of customer email addresses: an unkeyed
     * digest would be enumerable the same way an unkeyed PAN hash is.
     *
     * @param string $email
     * @return string|null
     */
    private function hashEmail(string $email): ?string
    {
        try {
            return $this->fingerprint->keyedHash($email);
        } catch (\Throwable $exception) {
            $this->logger->error(
                'Line Payment: could not key the email throttle dimension, skipping it: '
                . $exception->getMessage()
            );

            return null;
        }
    }

    /**
     * @param OrderAdapterInterface $order
     *
     * @return string|null
     */
    private function resolveEmail(OrderAdapterInterface $order): ?string
    {
        $address = $order->getBillingAddress();
        $email = $address ? $address->getEmail() : null;

        if (!is_string($email) || trim($email) === '') {
            return null;
        }

        return mb_strtolower(trim($email));
    }

    /**
     * The gateway order adapter does not expose the quote, but the payment's order model does.
     *
     * @param mixed $payment
     *
     * @return int|null
     */
    private function resolveQuoteId($payment): ?int
    {
        if (!is_object($payment) || !method_exists($payment, 'getOrder')) {
            return null;
        }

        $order = $payment->getOrder();

        if (!is_object($order) || !method_exists($order, 'getQuoteId')) {
            return null;
        }

        $quoteId = (int) $order->getQuoteId();

        return $quoteId > 0 ? $quoteId : null;
    }
}
