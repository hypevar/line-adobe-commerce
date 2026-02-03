<?php
/**
 * Copyright © 2024 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Controller\Ajax;

use Line\Payment\Api\GetPromotionsByBinActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Serialize\Serializer\Json as SerializerJson;

class Bin implements HttpPostActionInterface
{
    private RequestInterface $request;
    private GetPromotionsByBinActionInterface $promotions;
    private SerializerJson $serializer;
    private JsonFactory $resultJsonFactory;
    private RedirectFactory $resultRedirectFactory;
    private RawFactory $resultRawFactory;

    public function __construct(
        RequestInterface $request,
        GetPromotionsByBinActionInterface $action,
        SerializerJson $serializer,
        JsonFactory $resultJsonFactory,
        RawFactory $resultRawFactory
    ) {
        $this->request = $request;
        $this->promotions = $action;
        $this->serializer = $serializer;
        $this->resultRawFactory = $resultRawFactory;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    private function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('*/*/');

        return new InvalidRequestException(
            $resultRedirect,
            [new Phrase('Invalid Form Key. Please refresh the page.')]
        );
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

        try {
            $values = $this->serializer->unserialize($this->getRequest()->getContent());

            /** @var array $promotions */
            $promotions = $this->promotions->get($values['value']);

            if (!count($promotions)) {
                $response = [
                    'errors' => true,
                    'message' => __('No promotions found'),
                    'result' => []
                ];
            } else {
                $response = [
                    'errors' => false,
                    'result' => $promotions,
                    'message' => __('Promotions successfully retrieved')
                ];
            }

        } catch (LocalizedException $e) {
            $response = [
                'errors' => true,
                'message' => $e->getMessage(),
                'result' => []
            ];
        } catch (\Exception $e) {
            return $resultRaw->setHttpResponseCode(400);
        }

        /** @var Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }
}
