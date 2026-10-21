<?php

declare (strict_types = 1);

require_once __DIR__ . '/../Model/Reserva.php';
require_once __DIR__ . '/../Model/Estados.php';
require_once __DIR__ . '/../Model/TipoReservaEvento.php';
require_once __DIR__ . '/../Repositories/ReservaRepository.php';
require_once __DIR__ . '/../Repositories/ReservaEventoRepository.php';
require_once __DIR__ . '/../Exceptions/ReservaException.php';

class ReservaService
{
    private ReservaRepository $reservaRepository;
    private ReservaEventoRepository $reservaEventoRepository;

    public function __construct()
    {
        $this->reservaRepository = new ReservaRepository();
        $this->reservaEventoRepository = new ReservaEventoRepository();
    }

    /**
     * Busca una reserva por su ID y la devuelve convertida en un objeto Reserva.
     * @param int $id El ID de la reserva a buscar.
     * @return Reserva Devuelve un objeto Reserva si se encuentra.
     */
    public function obtenerReservaPorId(int $id): Reserva
    {
        if ($id <= 0) {
            throw new ValidacionException("El ID de la reserva debe ser un número positivo.");
        }

        $reserva = $this->reservaRepository->buscarPorId($id);

        if ($reserva === null) {
            throw new ReservaNotFoundException("No se encontró ninguna reserva con el ID: $id.");
        }

        return $reserva;
    }

    /**
     * Lista todas las reservas.
     * @param string|null $estadoStr Filtro por estado de la reserva.
     * @param string|null $fechaInicio Filtro por fecha de entrada (formato 'Y-m-d').
     * @param string|null $fechaFin Filtro por fecha de salida (formato 'Y-m-d').
     * @param string|null $nombreHuesped Filtro por nombre del huésped (búsqueda parcial).
     * @return array Devuelve un array de objetos Reserva
     */
    public function listarReservas(?string $estadoStr = null, ?string $fechaInicio = null, ?string $fechaFin = null, ?string $nombreHuesped = null): array
    {
        $estado = null;

        if ($estadoStr !== null && trim($estadoStr) !== '') {
            $estado = Estados::fromValue($estadoStr);
            if ($estado === null) {
                throw new ValidacionException("El estado proporcionado no es válido. Los estados válidos son: " . implode(", ", array_map(fn($e) => $e->name, Estados::cases())));
            }
        }

        $fechaInicioLimpia = $this->normalizarTextoOpcional($fechaInicio);
        $fechaFinLimpia = $this->normalizarTextoOpcional($fechaFin);

        if (($fechaInicioLimpia === null) !== ($fechaFinLimpia === null)) {
            throw new ValidacionException("Para filtrar por fecha debe enviar fecha_inicio y fecha_fin.");
        }

        if ($fechaInicioLimpia !== null && $fechaFinLimpia !== null) {
            $fechaInicioObj = $this->parsearFechaEstrica($fechaInicioLimpia, 'fecha_inicio');
            $fechaFinObj = $this->parsearFechaEstrica($fechaFinLimpia, 'fecha_fin');

            if ($fechaFinObj < $fechaInicioObj) {
                throw new ValidacionException("fecha_fin no puede ser anterior a fecha_inicio.");
            }
        }

        return $this->reservaRepository->buscarConFiltros($estado, $fechaInicioLimpia, $fechaFinLimpia, $nombreHuesped);
    }

    /**
     * Crea una nueva reserva en la base de datos.
     */
    public function crearReserva(
        string $nombreHuesped,
        string $emailHuesped,
        string $nombreAlojamiento,
        string $fechaEntradaStr,
        string $fechaSalidaStr,
        string $estadoStr,
        float $importe,
        string $notas = ''
    ): int {
        $nombreHuesped = trim($nombreHuesped);
        $emailHuesped = trim($emailHuesped);
        $nombreAlojamiento = trim($nombreAlojamiento);
        $notas = trim($notas);

        if ($nombreHuesped === '' || $emailHuesped === '' || $nombreAlojamiento === '') {
            throw new ValidacionException("nombre_huesped, email_huesped y nombre_alojamiento son obligatorios y no pueden estar vacíos.");
        }

        if (!filter_var($emailHuesped, FILTER_VALIDATE_EMAIL)) {
            throw new ValidacionException("El email proporcionado no tiene un formato válido.");
        }

        $estado = Estados::fromValue($estadoStr);
        if ($estado === null) {
            throw new ValidacionException("Estado no válido para la creación.");
        }

        $fechaEntrada = $this->parsearFechaEstrica($fechaEntradaStr, 'fecha_entrada');
        $fechaSalida  = $this->parsearFechaEstrica($fechaSalidaStr, 'fecha_salida');

        if ($fechaSalida <= $fechaEntrada) {
            throw new ValidacionException("La fecha de salida debe ser posterior a la fecha de entrada.");
        }

        if ($importe < 0) {
            throw new ValidacionException("El importe no puede ser negativo.");
        }

        $reserva = new Reserva(
            id: 0,
            nombre_huesped: $nombreHuesped,
            email_huesped: $emailHuesped,
            nombre_alojamiento: $nombreAlojamiento,
            fecha_entrada: $fechaEntrada,
            fecha_salida: $fechaSalida,
            estado: $estado,
            importe: $importe,
            notas: $notas
        );

        $reservaId = $this->reservaRepository->insertar($reserva);

        $this->reservaEventoRepository->insertar(
            $reservaId,
            TipoReservaEvento::CREACION,
            'Reserva creada con estado: ' . $estado->getEstado()
        );

        return $reservaId;
    }

    /**
     * Modifica el estado de una reserva.
     * @param int $id El ID de la reserva a modificar.
     * @param string $nuevoEstadoStr El nuevo estado de la reserva.
     * @return bool
     */
    public function modificarEstado(int $id, string $nuevoEstadoStr): bool
    {
        $this->obtenerReservaPorId($id);

        $nuevoEstadoStr = trim($nuevoEstadoStr);
        if ($nuevoEstadoStr === '') {
            throw new ValidacionException("El campo estado es obligatorio.");
        }

        $nuevoEstado = Estados::fromValue($nuevoEstadoStr);
        if ($nuevoEstado === null) {
            throw new ValidacionException("El nuevo estado no es válido. Los estados válidos son: " . implode(", ", array_map(fn($e) => $e->name, Estados::cases())));
        }

        $actualizada = $this->reservaRepository->actualizarEstado($id, $nuevoEstado);

        if ($actualizada) {
            $tipoEvento = match ($nuevoEstado) {
                Estados::CONFIRMADA => TipoReservaEvento::CONFIRMACION,
                Estados::CANCELADA => TipoReservaEvento::CANCELACION,
                default => TipoReservaEvento::MODIFICACION,
            };

            $this->reservaEventoRepository->insertar(
                $id,
                $tipoEvento,
                'Estado actualizado a: ' . $nuevoEstado->getEstado()
            );
        }

        return $actualizada;
    }

    private function normalizarTextoOpcional(?string $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        $valor = trim($valor);
        return $valor === '' ? null : $valor;
    }

    private function parsearFechaEstrica(string $fecha, string $campo): DateTime
    {
        $fecha = trim($fecha);
        $dt = DateTime::createFromFormat('Y-m-d', $fecha);
        $errores = DateTime::getLastErrors();

        $hayErroresParseo = $errores !== false
            && (($errores['warning_count'] ?? 0) > 0 || ($errores['error_count'] ?? 0) > 0);

        if ($dt === false || $hayErroresParseo || $dt->format('Y-m-d') !== $fecha) {
            throw new ValidacionException("El campo $campo debe tener formato YYYY-MM-DD.");
        }

        return $dt;
    }
}
