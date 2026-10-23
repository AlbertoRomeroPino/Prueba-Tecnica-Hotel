<?php

declare (strict_types = 1);

require_once __DIR__ . '/../Repositories/ReservaEventoRepository.php';
require_once __DIR__ . '/ReservaService.php';

class ReservaEventoService
{
    private ReservaEventoRepository $eventoRepository;
    private ReservaService $reservaService;

    public function __construct()
    {
        $this->eventoRepository = new ReservaEventoRepository();
        $this->reservaService = new ReservaService();
    }

    /**
     * Lista todos los eventos asociados a una reserva específica.
     * @param int $reservaId El ID de la reserva para la cual se desean
     * @return Reserva_evento[]
     */
    public function listarPorReservaId(int $reservaId): array
    {
        if ($reservaId <= 0) {
            throw new ValidacionException('El ID de la reserva debe ser un número positivo.');
        }

        $this->reservaService->obtenerReservaPorId($reservaId);

        return $this->eventoRepository->buscarPorReservaId($reservaId);
    }
}
