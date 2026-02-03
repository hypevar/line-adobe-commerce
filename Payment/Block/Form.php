<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */
declare(strict_types=1);

namespace Line\Payment\Block;

use Line\Payment\Model\Config as GatewayConfig;
use Magento\Backend\Model\Session\Quote;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Block\Form\Cc;
use Magento\Payment\Model\Config;

/**
 *
 */
class Form extends Cc
{
    protected $_template = 'Line_Payment::form/default.phtml';

    protected Quote $sessionQuote;
    protected Config $gatewayConfig;

    /**
     * @param Context $context
     * @param Config $config
     * @param Quote $sessionQuote
     * @param GatewayConfig $gatewayConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $config,
        Quote $sessionQuote,
        GatewayConfig $gatewayConfig,
        array $data = []
    ) {
        parent::__construct($context, $config, $data);

        $this->sessionQuote = $sessionQuote;
        $this->gatewayConfig = $gatewayConfig;
    }
}
