<?php
/**
 * Copyright © 2026 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Model\Sandbox;

use Line\Payment\Api\Data\ConfigInterface;

/**
 * Answers gateway calls from committed fixtures while the module runs in mock mode.
 *
 * Intercepting at the connector leaves the rest of the stack untouched: the real converters,
 * validators and throttle all run against the fixture exactly as they would against the gateway.
 */
abstract class AbstractConnectorPlugin
{
    protected ConfigInterface $config;
    protected OperationResolver $resolver;
    protected FixtureRepository $fixtures;

    public function __construct(
        ConfigInterface $config,
        OperationResolver $resolver,
        FixtureRepository $fixtures
    ) {
        $this->config = $config;
        $this->resolver = $resolver;
        $this->fixtures = $fixtures;
    }

    /**
     * Serves a fixture, or lets the real request through when mock mode is off.
     *
     * @param callable $proceed
     * @param string $path
     * @param mixed $body
     *
     * @return array
     */
    protected function intercept(callable $proceed, string $path, $body): array
    {
        if (!$this->config->isMockModeEnabled()) {
            return $proceed($path, $body);
        }

        $operation = $this->resolver->resolve($path);

        if ($operation === null) {
            return $proceed($path, $body);
        }

        $fixture = $this->fixtures->load($operation);

        return $this->respond((int) $fixture['status'], (array) $fixture['body']);
    }

    /**
     * Reproduces what the intercepted connector does with the fixture's HTTP status.
     *
     * @param int $status
     * @param array $body
     *
     * @return array
     */
    abstract protected function respond(int $status, array $body): array;
}
