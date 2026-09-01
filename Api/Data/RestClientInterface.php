<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Api\Data;

/**
 *
 */
interface RestClientInterface
{
    public const CONNECTOR_PUBLIC_KEY = 'public_key';
    public const CONNECTOR_PRIVATE_KEY = 'private_key';

    public const CONNECTOR_ENVIRONMENT_SANDBOX = 'test';
    public const CONNECTOR_ENVIRONMENT_PRODUCTION = 'prod';

    public const SUCCESS_STATUS_CODES = [200, 201, 204];

    public const CONNECTOR_SERVICE = 'PHP-MAGENTO';
    public const CONNECTOR_DEVELOPER = 'Line DEV Team';
}
