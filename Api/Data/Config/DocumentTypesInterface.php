<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Api\Data\Config;

/**
 *
*/
interface DocumentTypesInterface {

    /**#@+
     * @access public
     * @var string
     */
    public const TYPE_DNI = 'DNI';
    public const TYPE_CUIT = 'CUIT';
    public const TYPE_CUIL = 'CUIL';
    /**#@- */

    public const DOCUMENT_TYPE_OPTIONS_LIST = [
        self::TYPE_DNI => self::TYPE_DNI,
        self::TYPE_CUIT => self::TYPE_CUIT,
        self::TYPE_CUIL => self::TYPE_CUIL
    ];
}
