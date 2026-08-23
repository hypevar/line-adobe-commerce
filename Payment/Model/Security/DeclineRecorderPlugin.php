<?php
/**
 * Copyright © 2025 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Model\Security;

use Line\Payment\Api\Response\AttributeInterface;
use Line\Payment\Gateway\Validator\Pool;
use Line\Payment\Gateway\Validator\ResultProvider;
use Magento\Payment\Gateway\Validator\ResultInterface;

/**
 * Counts a decline once the gateway has answered.
 *
 * This cannot live in the response handler chain: GatewayCommand throws from processErrors() when
 * validation fails, so the handlers never run on a decline. The validator pool is the last place
 * that still has both the verdict and the payment.
 */
class DeclineRecorderPlugin
{
    private ResultProvider $resultProvider;
    private AttemptContextBuilder $contextBuilder;
    private AttemptRecorder $recorder;

    /**
     * @param ResultProvider $resultProvider
     * @param AttemptContextBuilder $contextBuilder
     * @param AttemptRecorder $recorder
     */
    public function __construct(
        ResultProvider $resultProvider,
        AttemptContextBuilder $contextBuilder,
        AttemptRecorder $recorder
    ) {
        $this->resultProvider = $resultProvider;
        $this->contextBuilder = $contextBuilder;
        $this->recorder = $recorder;
    }

    /**
     * @param Pool $subject
     * @param ResultInterface $result
     * @param array $validationSubject
     *
     * @return ResultInterface
     */
    public function afterValidate(
        Pool $subject,
        ResultInterface $result,
        array $validationSubject
    ): ResultInterface {
        if ($result->isValid() || !$this->isRealDecline($validationSubject)) {
            return $result;
        }

        $context = $this->contextBuilder->build($validationSubject);

        if ($context !== null) {
            $this->recorder->record($context);
        }

        return $result;
    }

    /**
     * Only a response that carries a gateway transaction identifier counts.
     *
     * This guard is what stands between the throttle and a store wide lockout: if the module's own
     * credentials or transport are broken, every request comes back as an error, and a throttle
     * counting those would lock out the entire customer base within minutes. Transport failures and
     * malformed responses are the module's problem, not the customer's.
     *
     * @param array $validationSubject
     *
     * @return bool
     */
    private function isRealDecline(array $validationSubject): bool
    {
        try {
            $response = $this->resultProvider->normalize($validationSubject);
        } catch (\Throwable $exception) {
            return false;
        }

        $identifier = $response[AttributeInterface::FIELD_IDENTIFIER] ?? null;

        return is_scalar($identifier) && (string) $identifier !== '';
    }
}
