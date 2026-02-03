<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Gateway\Request;

use Line\Payment\Api\Data\ConfigInterfaceFactory;
use Line\Payment\Api\Request\Attribute\EnteringModeInterface;
use Line\Payment\Api\Request\Attribute\SalesChannelInterface;
use Line\Payment\Api\Request\Attribute\TerminalTypeInterface;
use Line\Payment\Api\Request\BuilderInterface;

/**
 * Sales channel for this sales request
 */
class SalesChannelDataBuilder implements BuilderInterface
{
    private ConfigInterfaceFactory $config;

    public function __construct(
        ConfigInterfaceFactory $config
    ) {
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject): array
    {
        $config = $this->config->create();

        return [
            self::FIELD_SALES_CHANNEL => SalesChannelInterface::CHANNEL_WEB,
            self::FIELD_TERMINAL_SYSTEM => $config->getTerminalSystem(),
            self::FIELD_TERMINAL_TYPE => TerminalTypeInterface::TYPE_VIRTUAL,
            self::FIELD_ENTERING_MODE => EnteringModeInterface::MODE_WEB
        ];
    }
}
