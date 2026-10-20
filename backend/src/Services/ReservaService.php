<?php

declare (strict_types = 1);

require_once __DIR__ . '/../Model/Reserva.php';
require_once __DIR__ . '/../Model/Estados.php';
require_once __DIR__ . '/../Repositories/ReservaRepository.php';
require_once __DIR__ . '/../Exceptions/ReservaException.php';

class ReservaService
{
    private ReservaRepository $reservaRepository;

    public function __construct()
    {
        $this->reservaRepository = new ReservaRepository();
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

        return $this->reservaRepository->buscarConFiltros($estado, $fechaInicio, $fechaFin, $nombreHuesped);
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
        $estado = Estados::fromValue($estadoStr);
        if ($estado === null) {
            throw new ValidacionException("Estado no válido para la creación.");
        }

        $fechaEntrada = new DateTime($fechaEntradaStr);
        $fechaSalida  = new DateTime($fechaSalidaStr);

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

        return $this->reservaRepository->insertar($reserva);
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

        $nuevoEstado = Estados::fromValue($nuevoEstadoStr);
        if ($nuevoEstado === null) {
            throw new ValidacionException("El nuevo estado no es válido. Los estados válidos son: " . implode(", ", array_map(fn($e) => $e->name, Estados::cases())));
        }

        return $this->reservaRepository->actualizarEstado($id, $nuevoEstado);
    }
}
