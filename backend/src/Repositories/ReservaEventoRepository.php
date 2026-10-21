<?php
declare (strict_types = 1);

require_once __DIR__ . '/../Config/Database.php';
require_once __DIR__ . '/../Model/Reserva.php';
require_once __DIR__ . '/../Model/Reserva_evento.php';
require_once __DIR__ . '/../Model/Estados.php';
require_once __DIR__ . '/../Model/TipoReservaEvento.php';
require_once __DIR__ . '/../Exceptions/ReservaException.php';
require_once __DIR__ . '/../../utils/SqlQueries.php';

class ReservaEventoRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function insertar(int $reservaId, TipoReservaEvento $tipo, string $descripcion): int
    {
        $stmt = $this->db->prepare(ReservaEventoSql::INSERTAR);
        $stmt->execute([$reservaId, $tipo->name, $descripcion]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * @return Reserva_evento[]
     */
    public function buscarPorReservaId(int $reservaId): array
    {
        $stmt = $this->db->prepare(ReservaEventoSql::BUSCAR_POR_RESERVA_ID);
        $stmt->execute([$reservaId]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $eventos = [];

        foreach ($rows as $row) {
            $eventos[] = $this->mapearEvento($row);
        }

        return $eventos;
    }

    private function mapearEvento(array $row): Reserva_evento
    {
        $estado = Estados::fromValue((string) ($row['estado'] ?? ''));
        if ($estado === null) {
            throw new ValidacionException('Estado de reserva inválido en la base de datos.');
        }

        $tipo = TipoReservaEvento::fromValue((string) ($row['evento_tipo'] ?? ''));
        if ($tipo === null) {
            throw new ValidacionException('Tipo de evento de reserva inválido en la base de datos.');
        }

        $reserva = new Reserva(
            id: (int) ($row['id'] ?? 0),
            nombre_huesped: (string) ($row['nombre_huesped'] ?? ''),
            email_huesped: (string) ($row['email_huesped'] ?? ''),
            nombre_alojamiento: (string) ($row['nombre_alojamiento'] ?? ''),
            fecha_entrada: new DateTime((string) ($row['fecha_entrada'] ?? 'now')),
            fecha_salida: new DateTime((string) ($row['fecha_salida'] ?? 'now')),
            estado: $estado,
            importe: (float) ($row['importe'] ?? 0),
            notas: (string) ($row['notas'] ?? '')
        );

        $fechaEvento = new DateTime((string) ($row['evento_fecha'] ?? 'now'));

        return new Reserva_evento(
            id: (int) ($row['evento_id'] ?? 0),
            reserva: $reserva,
            tipo: $tipo,
            descripcion: (string) ($row['evento_descripcion'] ?? ''),
            fecha: $fechaEvento
        );
    }
}
