<?php
/**
 * Copyright © 2025 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Gateway\Request;

use Line\Payment\Model\Security\AttemptContextBuilder;
use Line\Payment\Model\Security\AttemptGuard;
use Magento\Framework\Exception\SecurityViolationException;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Pre-flight check for the card testing throttle. Contributes nothing to the request.
 *
 * It is a request builder rather than a validator because this has to run inside
 * GatewayCommand::execute() *before* client->placeRequest(): a blocked session must not trigger the
 * outbound call at all, which is what stops both the amplification and the per-transaction gateway
 * cost. Registered first in LinePaymentCaptureRequest, so it also short-circuits the promotions
 * lookup that the details builder would otherwise perform.
 *
 * The gateway validator pool cannot serve here - it validates the response - and a plugin on core's
 * PaymentProcessingRateLimiterInterface::limit() takes no arguments, so it has no BIN, no
 * fingerprint and no method to work with.
 */
class ThrottleGuardBuilder implements BuilderInterface
{
    private AttemptContextBuilder $contextBuilder;
    private AttemptGuard $guard;

    /**
     * @param AttemptContextBuilder $contextBuilder
     * @param AttemptGuard $guard
     */
    public function __construct(
        AttemptContextBuilder $contextBuilder,
        AttemptGuard $guard
    ) {
        $this->contextBuilder = $contextBuilder;
        $this->guard = $guard;
    }

    /**
     * @param array $buildSubject
     *
     * @return array
     * @throws SecurityViolationException
     */
    public function build(array $buildSubject): array
    {
        $context = $this->contextBuilder->build($buildSubject);

        if ($context !== null) {
            $this->guard->assertAllowed($context);
        }

        return [];
    }
}
