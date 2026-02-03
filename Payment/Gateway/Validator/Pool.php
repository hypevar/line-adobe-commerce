<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

/**
 * Validator Pool implementation for response evaluation
 */
class Pool extends AbstractValidator
{
    /**
     * @var ResultProvider $resultProvider
     */
    private $resultProvider;

    /**
     * @var ValidatorInterface[] $validators
     */
    private array $validators;

    /**
     * @param ResultInterfaceFactory $factory
     * @param ResultProvider $resultProvider
     * @param array $validators
     */
    public function __construct(
        ResultInterfaceFactory $factory,
        ResultProvider $resultProvider,
        array $validators
    ) {
        parent::__construct($factory);
        $this->resultProvider = $resultProvider;
        $this->validators = $validators;
    }

    /**
     * @param array $subject
     *
     * @return ResultInterface
     */
    public function validate(array $subject): ResultInterface
    {
        // depending on how it fails, may return a different signature
        // use case: payload sent was never a JSON object

        /** @var array $response */
        $response = $this->resultProvider->normalize($subject);

        foreach ($this->validators as $validator) {
            /** @var ResultInterface $result */
            $result = $validator->validate($response);

            // returning so whatever error defined in `error_mapping.xml`
            // can be displayed into the checkout
            if (!$result->isValid()) {
                return $result;
            }
        }

        // returning `true`, given no errors were detected by validators
        return $this->createResult(true);
    }
}
