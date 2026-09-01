<?php
/**
 * Copyright © 2024 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Controller\Ajax;

use Line\Payment\Api\GetEmittersActionInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Exception\LocalizedException;

class Emitters implements HttpGetActionInterface
{
    protected GetEmittersActionInterface $emitters;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    public function __construct(
        GetEmittersActionInterface $promos,
        JsonFactory $resultJsonFactory,
        RawFactory $resultRawFactory
    ) {
        $this->emitters = $promos;
        $this->resultRawFactory = $resultRawFactory;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    public function execute()
    {
        /** @var Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();

        $response = [
            'errors' => false,
            'message' => __('Emitters successfully retrieved')
        ];

        try {
            $emitters = $this->emitters->get();
            $response['result'] = $emitters;

        } catch (LocalizedException $e) {
            $response = [
                'errors' => true,
                'message' => $e->getMessage()
            ];
        } catch (\Exception $e) {
            $response = [
                'errors' => true,
                'message' => __('Emitters cannot be retrieved from external service at this moment')
            ];
        }

        /** @var Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }
}
