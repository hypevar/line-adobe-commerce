<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Model\Config;

use Line\Payment\Api\Data\GetModuleVersionActionInterface;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Component\ComponentRegistrar;

/**
 * Returns which version number does the module currently have
 */
class GetModuleVersionAction implements GetModuleVersionActionInterface
{
    protected ComponentRegistrarInterface $registrar;
    protected ReadFactory $reader;

    /**
     * @param ComponentRegistrarInterface $component
     * @param ReadFactory $reader
     */
    public function __construct(
        ComponentRegistrarInterface $component,
        ReadFactory $reader
    ) {
        $this->reader = $reader;
        $this->registrar = $component;
    }

    /**
     * @inheritDoc
     */
    public function get(): string
    {
        try {
            $path = $this->registrar->getPath(
                ComponentRegistrar::MODULE,
                self::MODULE_NAME
            );

            $directory = $this->reader->create($path);
            $content = $directory->readFile(self::COMPOSER_FILENAME);
            $data = json_decode($content);

            return !empty($data->version) ? $data->version : '0.0.0';

        } catch (\Exception $e) {
            // silence is golden
        }

        return '0';
    }
}
