<?php
/**
 * Copyright © 2024 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Controller\Ajax;

use InvalidArgumentException;
use Line\Payment\Api\GetPromotionsByBinActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Serialize\Serializer\Json as SerializerJson;
use Psr\Log\LoggerInterface;

class Bin implements HttpPostActionInterface, CsrfAwareActionInterface
{
    /**
     * Request field carrying the session form key.
     */
    private const FIELD_FORM_KEY = 'form_key';

    private RequestInterface $request;
    private GetPromotionsByBinActionInterface $promotions;
    private SerializerJson $serializer;
    private JsonFactory $resultJsonFactory;
    private RawFactory $resultRawFactory;
    private FormKey $formKey;
    private LoggerInterface $logger;

    /**
     * @param RequestInterface $request
     * @param GetPromotionsByBinActionInterface $action
     * @param SerializerJson $serializer
     * @param JsonFactory $resultJsonFactory
     * @param RawFactory $resultRawFactory
     * @param FormKey $formKey
     * @param LoggerInterface $logger
     */
    public function __construct(
        RequestInterface $request,
        GetPromotionsByBinActionInterface $action,
        SerializerJson $serializer,
        JsonFactory $resultJsonFactory,
        RawFactory $resultRawFactory,
        FormKey $formKey,
        LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->promotions = $action;
        $this->serializer = $serializer;
        $this->resultRawFactory = $resultRawFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->formKey = $formKey;
        $this->logger = $logger;
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
        /** @var Raw $result */
        $result = $this->resultRawFactory->create();
        $result->setHttpResponseCode(403);

        return new InvalidRequestException(
            $result,
            [new Phrase('Invalid Form Key. Please refresh the page.')]
        );
    }

    /**
     * The checkout posts a JSON document rather than a form, so the key is not among the request
     * parameters; it travels inside the body.
     *
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        $submitted = (string) $request->getParam(self::FIELD_FORM_KEY, '');

        if ($submitted === '') {
            $submitted = (string) ($this->readBody()[self::FIELD_FORM_KEY] ?? '');
        }

        if ($submitted === '') {
            return false;
        }

        return hash_equals((string) $this->formKey->getFormKey(), $submitted);
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

        try {
            $values = $this->readBody();

            /** @var array $promotions */
            $promotions = $this->promotions->get((string) ($values['value'] ?? ''));

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
        } catch (\Throwable $e) {
            $this->logger->error('BIN promotions request failed: ' . $e->getMessage());

            $response = [
                'errors' => true,
                'message' => __('Card promotions are not available right now. Please try again in a few minutes.'),
                'result' => []
            ];
        }

        /** @var Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }

    /**
     * @return array
     */
    private function readBody(): array
    {
        try {
            $values = $this->serializer->unserialize((string) $this->getRequest()->getContent());
        } catch (InvalidArgumentException $exception) {
            return [];
        }

        return is_array($values) ? $values : [];
    }
}
