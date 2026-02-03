<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Api\Request\Attribute;

/**
 *
 * @link https://line.net.ar/documentacion/#transacciones-ecommerce-presenciales
 */
interface EnteringModeInterface
{
    /**#@+
     * @access public
     * @var string
     */
    public const MODE_MANUAL = 'MANUAL';
    public const MODE_BAND = 'BANDA';
    public const MODE_WEB = 'WEB';
    public const MODE_CHIP = 'CHIP';
    public const MODE_CONTACTLESS = 'CONTACTLESS';
    /**#@-*/

}
