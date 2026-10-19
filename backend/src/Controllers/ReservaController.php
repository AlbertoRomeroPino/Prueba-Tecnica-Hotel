<?php
declare (strict_types = 1);

require_once __DIR__ . '/../services/ReservaService.php';

class ReservaController
{
    private ReservaService $service;

    public function __construct()
    {
        $this->service = new ReservaService();
    }

    /**
     * Lista todas las reservas con filtros opcionales.
     * @param Estados|null $estado Filtro por estado de la reserva.
     * @param string|null $fechaInicio Filtro por fecha de entrada (formato 'Y-m-d').
     * @param string|null $fechaFin Filtro por fecha de salida (formato 'Y-m-d').
     * @param string|null $nombreHuesped Filtro por nombre del huésped (búsqueda parcial).
     * @return void
     */
    public function listar(): void
    {
        try {
            // Recogemos parámetros GET para los filtros
            $estado      = $_GET['estado'] ?? null;
            $fechaInicio = $_GET['fecha_inicio'] ?? null;
            $fechaFin    = $_GET['fecha_fin'] ?? null;
            $nombre      = $_GET['nombre'] ?? null;

            $reservas = $this->service->listarReservas($estado, $fechaInicio, $fechaFin, $nombre);

            $data = array_map(function ($r) {
                return [
                    "id"                 => $r->getId(),
                    "nombre_huesped"     => $r->getNombreHuesped(),
                    "email_huesped"      => $r->getEmailHuesped(),
                    "nombre_alojamiento" => $r->getNombreAlojamiento(),
                    "fecha_entrada"      => $r->getFechaEntrada()->format('Y-m-d'),
                    "fecha_salida"       => $r->getFechaSalida()->format('Y-m-d'),
                    "estado"             => $r->getEstado()->getEstado(),
                    "importe"            => $r->getImporte(),
                    "notas"              => $r->getNotas(),
                ];
            }, $reservas);

            http_response_code(200);
            echo json_encode(["status" => "success", "data" => $data]);

        } catch (ReservaException $e) {
            http_response_code($e->getCode());
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Error interno: " . $e->getMessage()]);
        }
    }

    /**
     * Muestra el detalle de una reserva específica.
     * @param int $id El ID de la reserva a mostrar.
     * @return void
     */
    public function verDetalle(int $id): void {
        try {
            $reserva = $this->service->obtenerReservaPorId($id);

            $data = [
                "id" => $reserva->getId(),
                "nombre_huesped" => $reserva->getNombreHuesped(),
                "email_huesped" => $reserva->getEmailHuesped(),
                "nombre_alojamiento" => $reserva->getNombreAlojamiento(),
                "fecha_entrada" => $reserva->getFechaEntrada()->format('Y-m-d'),
                "fecha_salida" => $reserva->getFechaSalida()->format('Y-m-d'),
                "estado" => $reserva->getEstado()->getEstado(),
                "importe" => $reserva->getImporte(),
                "notas" => $reserva->getNotas()
            ];

            http_response_code(200);
            echo json_encode(["status" => "success", "data" => $data]);

        } catch (ReservaException $e) {
            http_response_code($e->getCode());
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Error interno: " . $e->getMessage()]);
        }
    }

    /**
     * Modifica el estado de una reserva específica.
     * @param int $id El ID de la reserva a modificar.
     * @return void
     */
    public function cambiarEstado(int $id): void {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $nuevoEstado = $input['estado'] ?? $_POST['estado'] ?? '';

            $this->service->modificarEstado($id, $nuevoEstado);

            http_response_code(200);
            echo json_encode(["status" => "success", "message" => "Estado actualizado correctamente."]);

        } catch (ReservaException $e) {
            http_response_code($e->getCode());
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Error interno: " . $e->getMessage()]);
        }
    }
}
