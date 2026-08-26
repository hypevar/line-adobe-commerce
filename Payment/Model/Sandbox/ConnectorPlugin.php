<?php
/**
 * Copyright © 2026 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Model\Sandbox;

use Line\Payment\Model\Client\Connector;

/**
 * Mock responses for the payment connector: sale, refund and emitters.
 */
class ConnectorPlugin extends AbstractConnectorPlugin
{
    /**
     * @param Connector $subject
     * @param callable $proceed
     * @param string $path
     * @param mixed $body
     *
     * @return array
     */
    public function aroundGet(Connector $subject, callable $proceed, $path, $body = []): array
    {
        return $this->intercept($proceed, (string) $path, $body);
    }

    /**
     * @param Connector $subject
     * @param callable $proceed
     * @param string $path
     * @param mixed $body
     *
     * @return array
     */
    public function aroundPost(Connector $subject, callable $proceed, $path, $body = []): array
    {
        return $this->intercept($proceed, (string) $path, $body);
    }

    /**
     * The payment connector logs a non-2xx answer and hands the body on for the validators to reject.
     *
     * @param int $status
     * @param array $body
     *
     * @return array
     */
    protected function respond(int $status, array $body): array
    {
        return $body;
    }
}
