<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Model\Client;

use Line\Payment\Api\GatewayResponseInterface;
use Line\Payment\Api\ResponseInterface;
use Line\Payment\Api\Response\AttributeDetailInterface as ResponseDetailInterface;
use Line\Payment\Api\Response\GatewayAttribute\DetailInterface;
use Line\Payment\Gateway\Api\ResponseFactory;
use Line\Payment\Gateway\Api\Response\DetailFactory as ResponseDetailFactory;
use Magento\Framework\Event\ManagerInterface;

class DataConverter
{
    protected ResponseFactory $response;
    protected ResponseDetailFactory $responseDetail;
    protected ManagerInterface $eventManager;

    /**
     * @param ResponseFactory $factory
     * @param ResponseDetailFactory $responseDetail
     */
    public function __construct(
        ResponseFactory $factory,
        ResponseDetailFactory $responseDetail,
        ManagerInterface $eventManager
    ) {
        $this->response = $factory;
        $this->responseDetail = $responseDetail;
        $this->eventManager = $eventManager;
    }

    /**
     * Converts Gateway response into a normalized object
     * to work with along the entire module
     *
     * @param array $response
     *
     * @return ResponseInterface
     */
    public function convert(array $response): ResponseInterface
    {
        /** @var ResponseInterface $object */
        $object = $this->response->create();

        $this->eventManager->dispatch('line_payment_data_converter_before', [
            'response' => $object,
            'raw_response' => $response
        ]);

        // retrieve gateway attribute matching list
        $mapping = GatewayResponseInterface::ATTRIBUTE_MATCHING;

        // if no status is set, we'll assume an exception happened within the Connector
        // In this case, object only contains a `message` error.
        // use case: api key is invalid
        if (!isset($response[GatewayResponseInterface::FIELD_STATUS])) {
            if ($response['Message']) {
                $object->setMessage($response['Message']);
                return $object;
            }
        }

        foreach ($response as $key => $value) {
            // skip detail object, we'll fill it up later
            if ($key === GatewayResponseInterface::FIELD_DETAIL) {
                continue;
            }

            // pick up internal attribute key
            $mk = $mapping[$key] ?? false;

            // if we've not mapped this attribute, move on
            if (!$mk) {
                // @TODO: leave a logging notification
                continue;
            }

            $object->setData($mk, $value);
        }

        // fill up Detail object
        if (isset($response[GatewayResponseInterface::FIELD_DETAIL])
            && count($response[GatewayResponseInterface::FIELD_DETAIL])
        ) {
            /** @var array $mapping Gateway Detail attribute matching list */
            $mapping = DetailInterface::ATTRIBUTE_MATCHING;

            /** @var ResponseDetailInterface $detail */
            $detail = $this->responseDetail->create();

            // extract the first element from the response
            $data = array_shift($response[GatewayResponseInterface::FIELD_DETAIL]);

            foreach ($data as $key => $value) {
                // pick up internal detail attribute key
                $mk = $mapping[$key] ?? false;

                // if we've not mapped this attribute, move on
                if (!$mk) {
                    continue;
                }

                $detail->setData($mk, $value);
            }

            $object->setDetail($detail);
        }

        $this->eventManager->dispatch(
            'line_payment_data_converter_after',
            ['response' => $object]
        );

        return $object;
    }
}
