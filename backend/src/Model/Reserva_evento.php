<?php

declare(strict_types=1);

class Reserva_evento
{
    private int $id;
    private Reserva $reserva;
    private TipoReservaEvento $tipo;
    private string $descripcion;
    private DateTime $fecha;

    public function __construct(
        int $id,
        Reserva $reserva,
        TipoReservaEvento $tipo,
        string $descripcion
    ) {
        $this->id = $id;
        $this->reserva = $reserva;
        $this->tipo = $tipo;
        $this->descripcion = $descripcion;
        $this->fecha = new DateTime();
    }

    public function getId(): int {
        return $this->id;
    }

    public function getReserva(): Reserva {
        return $this->reserva;
    }

    // Un atajo muy útil si necesitas el ID de la reserva rápidamente:
    public function getIdReserva(): int {
        return $this->reserva->getId();
    }

    public function getTipo(): TipoReservaEvento {
        return $this->tipo;
    }

    public function getDescripcion(): string {
        return $this->descripcion;
    }

    public function getFechaCreacion(): DateTime {
        return $this->fechaCreacion;
    }
}

?>