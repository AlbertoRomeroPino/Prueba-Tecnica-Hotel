<?php

declare (strict_types = 1);

require_once __DIR__ . '/../repositories/ReservaRepository.php';
require_once __DIR__ . '/../Exceptions/ExcepcionesReserva.php';

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
     * @return Reserva|null Devuelve un objeto Reserva si se encuentra, o null si no se encuentra.
     */
    public function obtenerReservaPorId(int $id): ?Reserva
    {
        if ($id <= 0) {
            throw new ValidacionException("El ID de la reserva debe ser un número positivo.");
        }
        $reserva = $this->reservaRepository->buscarPorId($id);

        if ($reserva === null) {
            throw new ReservaNotFoundException("No se encontró ninguna reserva con el ID: { $id }.");
        }

        return $reserva;
    }

    /**
     * Lista todas las reservas.
     * @param Estados|null $estadoStr Filtro por estado de la reserva.
     * @param string|null $fechaInicio Filtro por fecha de entrada (formato 'Y-m-d').
     * @param string|null $fechaFin Filtro por fecha de salida (formato 'Y-m-d').
     * @param string|null $nombreHuesped Filtro por nombre del huésped (búsqueda parcial).
     * @return array Devuelve un array de objetos Reserva
     */
    public function listarReservas(?Estados $estadoStr = null, ?string $fechaInicio = null, ?string $fechaFin = null, ?string $nombreHuesped = null): array
    {
        $estado = null;
        if ($estadoStr !== null && trim($estadoStr) !== '') {
            $estado = Estados::tryFrom(strtolower($estadoStr));
            if (! $estado) {
                throw new ValidacionException("El estado proporcionado no es válido. Los estados válidos son: " . implode(", ", array_map(fn($e) => $e->name, Estados::cases())));
            }
        }
        return $this->reservaRepository->buscarConFiltros($estado, $fechaInicio, $fechaFin, $nombreHuesped);
    }

    /**
     * Modifica el estado de una reserva.
     * @param int $id El ID de la reserva a modificar.
     * @param Estados $nuevoEstado El nuevo estado de la reserva.
     * @return void
     */
    public function modificarEstado(int $id, Estados $nuevoEstado): bool
    {
        $reserva = $this->obtenerReservaPorId($id);

        $nuevoEstado = Estados::tryFrom(strtolower($nuevoEstado->name));
        if (! $nuevoEstado) {
            throw new ReservaNotFoundException("El nuevo estado no es valido. Los estados válidos son: " . implode(", ", array_map(fn($e) => $e->name, Estados::cases())));
        }

        return $this->reservaRepository->actualizarEstado($id, $nuevoEstado);
    }
}
