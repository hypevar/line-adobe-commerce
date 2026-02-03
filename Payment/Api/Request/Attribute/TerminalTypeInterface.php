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
interface TerminalTypeInterface
{
    /**#@+
     * @access public
     * @var string
     */
    public const TYPE_PHYSICAL = 'FISICA';
    public const TYPE_VIRTUAL = 'VIRTUAL';
    /**#@-*/

}
