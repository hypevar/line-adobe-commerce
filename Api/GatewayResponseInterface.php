<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Api;

use Line\Payment\Api\Response\GatewayAttribute\DetailInterface;
use Line\Payment\Api\Response\GatewayAttributeInterface;

/**
 *
 */
interface GatewayResponseInterface extends GatewayAttributeInterface
{
    /**
     * @return string
     */
    public function getIdentificador(): string;

    /**
     * @param string $value
     *
     * @return self
     */
    public function setIdentificador(string $value): self;

    /**
     * @return string
     */
    public function getIdentificadorCliente(): string;

    /**
     * @param string $value
     *
     * @return self
     */
    public function setIdentificadorCliente(string $value): self;

    /**
     * @return string
     */
    public function getIdentificadorClienteOriginal(): string;

    /**
     * @param string $value
     *
     * @return self
     */
    public function setIdentificadorClienteOriginal(string $value): self;

    /**
     * @return string
     */
    public function getEstado(): string;

    /**
     * @param string $value
     *
     * @return self
     */
    public function setEstado(string $value): self;

    /**
     * @return int
     */
    public function getCodigoError(): int;

    /**
     * @param int $value
     *
     * @return self
     */
    public function setCodigoError(int $value): self;

    /**
     * @return string
     */
    public function getMensaje(): string;

    /**
     * @param string $value
     *
     * @return self
     */
    public function setMensaje(string $value): self;

    /**
     * @return string
     */
    public function getMensajeFormato(): string;

    /**
     * @param string $value
     *
     * @return self
     */
    public function setMensajeFormato(string $value): self;

    /**
     * @return string
     */
    public function getNumeroTarjeta(): string;

    /**
     * @param string $value
     *
     * @return self
     */
    public function setNumeroTarjeta(string $value): self;

    /**
     * @return string
     */
    public function getNumeroCuenta(): string;

    /**
     * @param string $value
     *
     * @return self
     */
    public function setNumeroCuenta(string $value): self;

    /**
     * @return string
     */
    public function getModoIngreso(): string;

    /**
     * @param string $value
     *
     * @return self
     */
    public function setModoIngreso(string $value): self;

    /**
     * @return string
     */
    public function getVTEResult(): string;

    /**
     * @param string $value
     *
     * @return self
     */
    public function setVTEResult(string $value): self;

    /**
     * @return string
     */
    public function getCodigoEstado(): string;

    /**
     * @param string $value
     *
     * @return self
     */
    public function setCodigoEstado(string $value): self;

    /**
     * @return string
     */
    public function getAntiFraude(): string;

    /**
     * @param string $value
     *
     * @return self
     */
    public function setAntiFraude(string $value): self;

    /**
     * @return string
     */
    public function getToken(): string;

    /**
     * @param string $value
     *
     * @return self
     */
    public function setToken(string $value): self;

    /**
     * @return string
     */
    public function getFechaExpiracion(): string;

    /**
     * @param string $value
     *
     * @return self
     */
    public function setFechaExpiracion(string $value): self;

    /**
     * @return string
     */
    public function getMarca(): string;

    /**
     * @param string $value
     *
     * @return self
     */
    public function setMarca(string $value): self;

    /**
     * @return string
     */
    public function getIdentificadorSoftDescriptor(): string;

    /**
     * @param string $value
     *
     * @return self
     */
    public function setIdentificadorSoftDescriptor(string $value): self;

    /**
     * @return DetailInterface|null
     */
    public function getDetalle(): DetailInterface|null;

    /**
     * @param DetailInterface $data
     *
     * @return self
     */
    public function setDetalle(DetailInterface $data): self;
}
