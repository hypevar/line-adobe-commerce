<?php
/**
 * Copyright © 2026 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Model\Sandbox;

use Line\Payment\Api\Data\ConfigInterface;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Serialize\Serializer\Json;
use Random\RandomException;

/**
 * Reads the committed gateway responses that mock mode answers with.
 */
class FixtureRepository
{
    private const MODULE_NAME = 'Line_Payment';
    private const FIXTURE_PATH = '/Sandbox/fixtures/';
    private const STATUS_KEY = '_httpStatus';

    private ConfigInterface $config;
    private ComponentRegistrar $registrar;
    private File $driver;
    private Json $serializer;

    public function __construct(
        ConfigInterface $config,
        ComponentRegistrar $registrar,
        File $driver,
        Json $serializer
    ) {
        $this->config = $config;
        $this->registrar = $registrar;
        $this->driver = $driver;
        $this->serializer = $serializer;
    }

    /**
     * Loads one fixture for an operation as `['status' => int, 'body' => array]`.
     *
     * @param string $operation
     *
     * @throws LocalizedException
     *
     * @return array
     */
    public function load(string $operation): array
    {
        $file = $this->pick($operation);
        $decoded = $this->serializer->unserialize($this->driver->fileGetContents($file));

        if (!is_array($decoded)) {
            throw new LocalizedException(
                __('The sandbox fixture %1 does not contain a JSON object.', $file)
            );
        }

        $status = (int) ($decoded[self::STATUS_KEY] ?? 200);
        unset($decoded[self::STATUS_KEY]);

        return ['status' => $status, 'body' => $decoded];
    }

    /**
     * Returns the configured scenario when it exists for this operation, otherwise a random one.
     *
     * Fixtures named `error-*` are excluded from the random pool so mock mode is usable by
     * default; they are reachable by naming them in the Mock Scenario field.
     *
     * @param string $operation
     *
     * @return string
     * @throws RandomException
     *
     * @throws LocalizedException
     */
    private function pick(string $operation): string
    {
        $directory = $this->registrar->getPath(ComponentRegistrar::MODULE, self::MODULE_NAME)
            . self::FIXTURE_PATH
            . $operation;

        $scenario = $this->config->getMockScenario();

        if ($scenario !== '') {
            $forced = $directory . '/' . $scenario . '.json';

            if ($this->driver->isExists($forced)) {
                return $forced;
            }
        }

        $files = $this->driver->isDirectory($directory)
            ? $this->driver->search('*.json', $directory)
            : [];

        if ($files === []) {
            throw new LocalizedException(
                __('No sandbox fixture is available for the "%1" operation.', $operation)
            );
        }

        $happy = array_values(
            array_filter($files, static fn (string $file): bool => !str_starts_with(basename($file), 'error-'))
        );

        $pool = $happy === [] ? $files : $happy;

        return $pool[random_int(0, count($pool) - 1)];
    }
}
