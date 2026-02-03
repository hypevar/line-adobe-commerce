<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Api\Data;

/**
 * Returns current `composer.json` version number
 */
interface GetModuleVersionActionInterface
{
    /**#@+
     * @var string
     */
    /**
     * Module version name as specified in `registration.php`
     */
    public const MODULE_NAME = 'Line_Payment';
    /**
     * Composer JSON file name
     */
    public const COMPOSER_FILENAME = 'composer.json';
    /**#@- */

    /**
     * Returns the current module's version within the composer json file
     *
     * @return string
     */
    public function get(): string;
}
