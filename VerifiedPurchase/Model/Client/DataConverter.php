<?php
/**
 * Copyright © 2024 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\VerifiedPurchase\Model\Client;

use Line\VerifiedPurchase\Gateway\Api\ResponseFactory;
use Line\VerifiedPurchase\Api\Verification\ResponseInterface;
use Line\VerifiedPurchase\Api\Verification\Gateway\Response\AttributeInterface as GatewayAttributes;

/**
 * Converts a Gateway Verification response into an object
 */
class DataConverter
{
    /**
     * @var ResponseFactory
     */
    protected ResponseFactory $response;

    /**
     * @param ResponseFactory $factory
     */
    public function __construct(
        ResponseFactory $factory
    ) {
        $this->response = $factory;
    }

    /**
     * Converts Gateway response into a normalized object to work with along the entire module
     *
     * @param array $data
     *
     * @return ResponseInterface
     */
    public function convert(array $data): ResponseInterface
    {
        /** @var ResponseInterface $object */
        $response = $this->response->create();

        $validated = isset($data[GatewayAttributes::FIELD_VALIDATED])
            ? (bool) $data[GatewayAttributes::FIELD_VALIDATED]
            : false;

        $response->setValidated($validated);

        $response->setVerifiedPurchaseId(
            $data[GatewayAttributes::FIELD_VERIFIED_PURCHASE_ID]
        );

        // if validated, capture transaction information
        // otherwise, capture error message
        if ($validated) {
            $response->setTransactionIdentifier(
                $data[GatewayAttributes::FIELD_TRANSACTION_IDENTIFIER]
            );

            $response->setBuyerIdentifier(
                $data[GatewayAttributes::FIELD_BUYER_IDENTIFIER]
            );
        } else {
            $response->setMessage(
                $data[GatewayAttributes::FIELD_MESSAGE]
            );
        }

        return $response;
    }
}
