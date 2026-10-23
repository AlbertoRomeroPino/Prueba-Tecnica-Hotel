<?php

declare (strict_types = 1);

require_once __DIR__ . '/Estados.php';
require_once __DIR__ . '/../Exceptions/ReservaException.php';

class Reserva
{
    private int $id;
    private string $nombre_huesped;
    private string $email_huesped;
    private string $nombre_alojamiento;
    private DateTime $fecha_entrada;
    private DateTime $fecha_salida;
    private Estados $estado;
    private float $importe;
    private string $notas;
    private DateTime $fecha_creacion;

    public function __construct(
        int $id,
        string $nombre_huesped,
        string $email_huesped,
        string $nombre_alojamiento,
        DateTime $fecha_entrada,
        DateTime $fecha_salida,
        Estados $estado,
        float $importe,
        string $notas = ''
    ) {
        $this->id                 = $id;
        $this->nombre_huesped     = $nombre_huesped;
        $this->email_huesped      = $email_huesped;
        $this->nombre_alojamiento = $nombre_alojamiento;
        $this->fecha_entrada      = $fecha_entrada;
        $this->fecha_salida       = $fecha_salida;
        $this->estado             = $estado;
        $this->importe            = $importe;
        $this->notas              = $notas;
        $this->fecha_creacion     = new DateTime();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNombreHuesped(): string
    {
        return $this->nombre_huesped;
    }

    public function getEmailHuesped(): string
    {
        return $this->email_huesped;
    }

    public function getNombreAlojamiento(): string
    {
        return $this->nombre_alojamiento;
    }

    public function getFechaEntrada(): DateTime
    {
        return $this->fecha_entrada;
    }

    public function getFechaSalida(): DateTime
    {
        return $this->fecha_salida;
    }

    public function getEstado(): Estados
    {
        return $this->estado;
    }

    public function getImporte(): float
    {
        return $this->importe;
    }

    public function getNotas(): string
    {
        return $this->notas;
    }

    public function getFechaCreacion(): DateTime
    {
        return $this->fecha_creacion;
    }

    public function setNombreHuesped(string $nombreHuesped): void
    {
        $this->nombre_huesped = $nombreHuesped;
    }

    public function setEmailHuesped(string $emailHuesped): void
    {
        if (! filter_var($emailHuesped, FILTER_VALIDATE_EMAIL)) {
            throw new ValidacionException("El email proporcionado no tiene un formato válido.");
        }

        $this->email_huesped = $emailHuesped;
    }

    public function setNombreAlojamiento(string $nombreAlojamiento): void
    {
        $this->nombre_alojamiento = $nombreAlojamiento;
    }

    public function setFechaEntrada(DateTime $fechaEntrada): void
    {
        $this->fecha_entrada = $fechaEntrada;
    }

    public function setFechaSalida(DateTime $fechaSalida): void
    {
        if ($fechaSalida <= $this->fecha_entrada) {
            throw new ValidacionException("La fecha de salida debe ser posterior a la de entrada.");
        }

        $this->fecha_salida = $fechaSalida;
    }

    public function setEstado(Estados $estado): void
    {
        $this->estado = $estado;
    }

    public function setImporte(float $importe): void
    {
        if ($importe < 0) {
            throw new ValidacionException("El importe no puede ser negativo.");
        }

        $this->importe = $importe;
    }

    public function setNotas(string $notas): void
    {
        $this->notas = $notas;
    }
}
