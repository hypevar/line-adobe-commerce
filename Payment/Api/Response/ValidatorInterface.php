<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Api\Response;

use Magento\Payment\Gateway\Validator\ResultInterface;

/**
 *
 */
interface ValidatorInterface
{
    /**
     * @param array $validationSubject
     *
     * @return ResultInterface
     */
    public function validate(array $validationSubject): ResultInterface;
}
