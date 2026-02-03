<?php
/**
 * Copyright © 2024 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Controller\Ajax;

use Line\Payment\Api\GetPromotionsActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json as SerializerJson;

class Promotions implements HttpPostActionInterface
{
    private RequestInterface $request;
    private GetPromotionsActionInterface $promotions;
    private SerializerJson $serializer;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    public function __construct(
        RequestInterface $request,
        GetPromotionsActionInterface $promos,
        SerializerJson $serializer,
        JsonFactory $resultJsonFactory,
        RawFactory $resultRawFactory
    ) {
        $this->request = $request;
        $this->promotions = $promos;
        $this->serializer = $serializer;
        $this->resultRawFactory = $resultRawFactory;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    private function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public function execute()
    {
        $response = [
            'errors' => true,
            'message' => __('Forbidden request')
        ];

        if (!$this->getRequest()->isPost()) {
            return $response;
        }

        /** @var Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();

        $response = [
            'errors' => false,
            'message' => __('Promotions successfully retrieved')
        ];

        try {
            $values = $this->serializer->unserialize($this->getRequest()->getContent());

            /** @var array $promotions */
            $promotions = $this->promotions->get($values['value']);

            if (!count($promotions)) {
                $response['errors'] = true;
                $response['message'] = __('No Promotions available');
            } else {
                $response = [
                    'errors' => false,
                    'result' => $promotions,
                    'message' => __('Promotions successfully retrieved')
                ];
            }

            $response['result'] = $promotions;

        } catch (LocalizedException $e) {
            $response = [
                'errors' => true,
                'message' => $e->getMessage(),
            ];
        } catch (\Exception $e) {
            return $resultRaw->setHttpResponseCode(400);
        }

        /** @var Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }
}
