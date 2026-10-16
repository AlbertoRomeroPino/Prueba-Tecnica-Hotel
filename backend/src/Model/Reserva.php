<?php



declare(strict_types=1);

class Reserva
{
    private int $id;
    private string $nombre_huesped;
    private string $email_huesped;
    private string $nombre_alojamiento;
    private DateTime $fecha_entrada;
    private DateTime $fecha_salida;
    private Estados $estado;    // pendiente-confirmada-cancelada
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
        string $notas = '' // valor por defecto vacío
    ) {
        $this->id = $id;
        $this->nombre_huesped = $nombre_huesped;
        $this->email_huesped = $email_huesped;
        $this->nombre_alojamiento = $nombre_alojamiento;
        $this->fecha_entrada = $fecha_entrada;
        $this->fecha_salida = $fecha_salida;
        $this->estado = $estado;
        $this->importe = $importe;
        $this->notas = $notas;
        $this->fecha_creacion = new DateTime();
    }

    // --------------------------------------------------

    public function getId(): int {
        return $this->id;
    }

    public function getNombreHuesped(): string {
        return $this->nombreHuesped;
    }

    public function getEmailHuesped(): string {
        return $this->emailHuesped;
    }

    public function getNombreAlojamiento(): string {
        return $this->nombreAlojamiento;
    }

    public function getFechaEntrada(): DateTime {
        return $this->fechaEntrada;
    }

    public function getFechaSalida(): DateTime {
        return $this->fechaSalida;
    }

    public function getEstado(): Estados {
        return $this->estado;
    }

    public function getImporte(): float {
        return $this->importe;
    }

    public function getNotas(): string {
        return $this->notas;
    }

    public function getFechaCreacion(): DateTime {
        return $this->fechaCreacion;
    }

    //----------------------------------------------------

    public function setNombreHuesped(string $nombreHuesped): void {
        $this->nombreHuesped = $nombreHuesped;
    }

    public function setEmailHuesped(string $emailHuesped): void {
        if (!filter_var($emailHuesped, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("El email proporcionado no es válido.");
        } else {
            $this->emailHuesped = $emailHuesped;
        }
    }

    public function setNombreAlojamiento(string $nombreAlojamiento): void {
        $this->nombreAlojamiento = $nombreAlojamiento;
    }

    public function setFechaEntrada(DateTime $fechaEntrada): void {
        $this->fechaEntrada = $fechaEntrada;
    }

    public function setFechaSalida(DateTime $fechaSalida): void {
        // Opcional: Validar que la fecha de salida sea posterior a la de entrada
        if ($fechaSalida <= $this->fechaEntrada) {
            throw new Exception("La fecha de salida debe ser posterior a la de entrada.");
        }
        $this->fechaSalida = $fechaSalida;
    }

    public function setEstado(Estados $estado): void {
        $this->estado = $estado;
    }

    public function setImporte(float $importe): void {
        if ($importe < 0) {
            throw new Exception("El importe no puede ser negativo.");
        }
        $this->importe = $importe;
    }

    public function setNotas(string $notas): void {
        $this->notas = $notas;
    }

}
?>