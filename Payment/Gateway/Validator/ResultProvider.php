<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Gateway\Validator;

use Line\Payment\Gateway\DataReader;
use Magento\Framework\DataObject;

/**
 * Normalizes Gateway response object
 */
class ResultProvider
{
    /**
     * @var DataReader $reader
     */
    private $reader;

    /**
     * @param DataReader $reader
     */
    public function __construct( DataReader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @param array $validationSubject
     * @return array
     */
    public function normalize(array $validationSubject): array
    {
        $response = $this->reader->readResponse($validationSubject);

        if (isset($response['object'])) {
            $response = $response['object'];
        }

        /** @var array|DataObject */
        return is_array($response)
            ? $response
            : $response->getData();
    }
}
