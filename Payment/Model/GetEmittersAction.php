<?php
/**
 * Copyright © 2024 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Model;

use Line\Payment\Api\GetEmittersActionInterface;
use Line\Payment\Model\Adapter;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

/**
 *
 */
class GetEmittersAction implements GetEmittersActionInterface
{
    protected Adapter $client;
    protected LoggerInterface $log;

    public const ENDPOINT_URL = '/creditcard/emisores';

    public const CHANNEL_WEB = 'WEB';
    public const CHANNEL_POS = 'PDV';

    /**
     * @param Adapter $client
     * @param LoggerInterface $logger
     */
    public function __construct(
        Adapter $client,
        LoggerInterface $logger
    ) {
        $this->client = $client;
        $this->log = $logger;
    }

    /**
     * Retrieves emitters from Gateway Service
     *
     * @return array
     */
    public function get(): array
    {
        $emitters = [];

        try {
            // retrieve emitters for WEB
            $endpoint = sprintf(self::ENDPOINT_URL . '/%s', self::CHANNEL_WEB);

            $emitters = $this->client->call('get', $endpoint, [], false);

            if (!$emitters) {
                $this->log->debug('Emitters: no records received from external service: ' . $endpoint);
                throw new LocalizedException(__('No emitters were received from external service'));
            }

            $this->log->debug('Emisores WEB', $emitters);

            return $this->convert($emitters);

        } catch (\Exception $e) {
            $this->log->error($e->getMessage());

            throw $e;
        }

        return $emitters;
    }

    /**
     * Format response payload into a standard schema
     *
     * @param array $payload
     *
     * @return array
     */
    private function convert(array $payload): array
    {
        $emitters = [];

        foreach ($payload as $emitter) {
            $cardFormat = $emitter['FormatoTarjeta'];
            $length = strlen(str_replace(' ', '', $cardFormat));

            // build up `length` and `gaps` values
            $groups = explode(" ", $cardFormat);
            $values = array_map(static function ($value) {
                return strlen($value);
            }, $groups);

            $gaps = []; $previous = 0;

            // remove last one so it isn't taken into account
            // (we need to calculate gaps, not total groups of numbers)
            array_pop($values);

            foreach ($values as $value) {
                array_push($gaps, $value + $previous);
                $previous = $value + $previous;
            }

            array_push($emitters, [
                'validateLuhn' => (bool) $emitter['ValidateLuhnCheck'],
                'cardBrand' => $emitter['CodigoEntidad'],
                'cardType' => $emitter['TarjetaTipo'],

                'title' => $emitter['Nombre'],
                'type' => $emitter['CodigoEntidad'],
                'pattern' => $emitter['Rango'],
                'lengths' => [$length],
                'gap' => $gaps,
                'code' => [
                    'name' => 'CVC',
                    'size' => (int) $emitter['CvcSize']
                ]
            ]);
        }

        return $emitters;
    }
}
