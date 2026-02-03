<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Api\Response;

interface StatusCodeInterface
{
    /**#@+
     * @access public
     * @var string
     */
    public const STATUS_CODE_APPROVED = 'approved';
    public const STATUS_CODE_REJECTED = 'rejected';
    /**#@-*/
}
