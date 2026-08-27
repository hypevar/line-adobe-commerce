<?php
/**
 * Copyright © 2026 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Model\Sandbox;

use Line\Payment\Model\Promotions\Connector;
use Line\Payment\Model\Promotions\Exception\PromotionsUnavailable;

/**
 * Mock responses for the promotions connector.
 */
class PromotionsConnectorPlugin extends AbstractConnectorPlugin
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
     * The promotions connector raises on any non-2xx answer rather than returning the body.
     *
     * @param int $status
     * @param array $body
     *
     * @throws PromotionsUnavailable
     *
     * @return array
     */
    protected function respond(int $status, array $body): array
    {
        if ($status < 200 || $status >= 300) {
            throw new PromotionsUnavailable(
                __('The promotions service answered with HTTP %1.', $status),
                null,
                $status
            );
        }

        return $body;
    }
}
