<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Block\Adminhtml;

use Line\Payment\Api\Data\Checkout\PaymentAttributeInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Block\Info\Cc;
use Magento\Payment\Model\Config as PaymentConfig;
use Magento\Framework\DataObject;

/**
 *
 */
class Info extends Cc
{
    protected PaymentConfig $paymentConfig;
    protected State $state;

    /**
     * @param Context $context
     * @param PaymentConfig $paymentConfig
     * @param State $state
     * @param array $data
     */
    public function __construct(
        Context $context,
        PaymentConfig $config,
        State $state,
        array $data = []
    ) {
        parent::__construct($context, $config, $data);

        $this->paymentConfig = $config;
        $this->state = $state;
    }

    /**
     * @return string
     */
    public function getCcTypeName()
    {
        return $this->getInfo()->getCcType() ?? __('N/A');
    }

    /**
     * @return int
     */
    public function hasCcExpDate()
    {
        return (int) $this->getInfo()->getCcExpMonth() || (int)$this->getInfo()->getCcExpYear();
    }

    /**
     * @return \DateTime
     */
    public function getCcExpDate()
    {
        $date = new \DateTime('now', new \DateTimeZone($this->_localeDate->getConfigTimezone()));
        $date->setDate(
            $this->getInfo()->getCcExpYear(),
            $this->getInfo()->getCcExpMonth() + 1,
            0
        );

        return $date;
    }

    /**
     * @param DataObject|array $transport
     * @return DataObject
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        if (null !== $this->_paymentSpecificInformation) {
            return $this->_paymentSpecificInformation;
        }

        $transport = parent::_prepareSpecificInformation($transport);
        $data = [];

        // Credit Card type
        if ($ccType = $this->getCcTypeName()) {
            $string = __('Credit Card Type')->render();

            $data[$string] = $ccType;
        }

        // Last four digits
        if ($this->getInfo()->getCcLast4()) {
            $string = __('Credit Card Number')->render();

            $data[$string] = sprintf('xxxx-%s', $this->getInfo()->getCcLast4());
        }

        // Installments
        $installments = $installments = $this->getInfo()
            ->getAdditionalInformation(
                PaymentAttributeInterface::PAYMENT_INSTALLMENTS
            );

        if ($installments) {
            $string = __('Installments')->render();
            $data[$string] = $installments;
        }

        // Credit Card Status and description
        $ccStatus = (int) $this->getInfo()->getCcStatus();

        if ($ccStatus >= 0) {
            $string = __('Status')->render();
            $data[$string] = __('code %1', $ccStatus);

            if ($ccStatusDescription = $this->getInfo()->getCcStatusDescription()) {
                $string = __('Status Description')->render();
                $data[$string] = sprintf('%s', $ccStatusDescription);
            }
        }

        // Expand information if we're in admin area
        // cannot use `getIsSecureMode()` given is checking against the wrong Area name
        if ($this->state->getAreaCode() == Area::AREA_ADMINHTML) {
            // Credit Card expiration month and year
            if ($month = $this->getInfo()->getCcExpMonth()) {
                $string = __('Credit Card Exp Month')->render();
                $data[$string] = $month;
            }

            if ($year = $this->getInfo()->getCcExpYear()) {
                $string = __('Credit Card Exp Year')->render();
                $data[$string] = $year;
            }

            /**
             * Credit Card Authorization Code
             *
             * @see \Line\Payment\Gateway\Response\PaymentInformationHandler
             */
            $ccAuthCode = $this->getInfo()->getAdditionalInformation(
                PaymentAttributeInterface::CREDIT_CARD_AUTORIZATION_CODE
            );

            if ($ccAuthCode) {
                $string = __('Authorization Code')->render();
                $data[$string] = $ccAuthCode;
            }
        }

        return $transport->setData(array_merge($data, $transport->getData()));
    }

    /**
     * @param string $year
     * @param string $month
     *
     * @return string
     */
    protected function _formatCardDate($year, $month)
    {
        return sprintf('%s/%s', sprintf('%02d', $month), $year);
    }
}
