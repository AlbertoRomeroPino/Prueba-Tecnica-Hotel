<?php
declare (strict_types = 1);

require_once __DIR__ . '/../Services/ReservaEventoService.php';

class ReservaEventoController
{
    private ReservaEventoService $service;

    public function __construct()
    {
        $this->service = new ReservaEventoService();
    }

    /**
     * Lista todos los eventos asociados a una reserva específica.
     * @param int $reservaId El ID de la reserva para la cual se desean listar los eventos.
     * @return void
     */
    public function listarPorReserva(int $reservaId): void
    {
        try {
            $eventos = $this->service->listarPorReservaId($reservaId);

            $data = array_map(function (Reserva_evento $evento) {
                return [
                    'id' => $evento->getId(),
                    'reserva_id' => $evento->getIdReserva(),
                    'tipo' => $evento->getTipo()->value,
                    'descripcion' => $evento->getDescripcion(),
                    'fecha_creacion' => $evento->getFechaCreacion()->format('Y-m-d H:i:s'),
                ];
            }, $eventos);

            http_response_code(200);
            echo json_encode(['status' => 'success', 'data' => $data], JSON_UNESCAPED_UNICODE);
        } catch (ReservaException $e) {
            http_response_code($e->getCode());
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Error interno del servidor.']);
        }
    }
}
