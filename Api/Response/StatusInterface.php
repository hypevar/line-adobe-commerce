<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Api\Response;

interface StatusInterface
{
    /**#@+
     * @access public
     * @var string
     */
    public const STATUS_AUTHORIZED = 'AUTORIZADA';
    public const STATUS_ANNULLED = 'ANULADA';
    public const STATUS_CONFIGURATION_ERROR = "ERRORCONFIGURACION";
    public const STATUS_ERROR = "ERROR";
    public const STATUS_NOT_AUTHORIZED = "NOAUTORIZADA";
    public const STATUS_PENDING_ANNULLMENT = "PENDIENTEANULACION";
    /**#@-*/
}
