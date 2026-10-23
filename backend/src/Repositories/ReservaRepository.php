<?php
declare (strict_types = 1);

require_once __DIR__ . '/../Config/Database.php';
require_once __DIR__ . '/../Model/Reserva.php';
require_once __DIR__ . '/../Model/Estados.php';
require_once __DIR__ . '/../Exceptions/ReservaException.php';
require_once __DIR__ . '/../../utils/SqlQueries.php';

class ReservaRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Busca una reserva por su ID y la devuelve convertida en un objeto Reserva.
     * @param int $id El ID de la reserva a buscar.
     * @return Reserva|null Devuelve un objeto Reserva si se encuentra, o null si no se encuentra.
     */
    public function buscarPorId(int $id): ?Reserva
    {
        $stmt = $this->db->prepare(ReservaSql::BUSCAR_POR_ID);
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (! $data) {
            return null;
        }

        return $this->mapearObjetoReserva($data);
    }

    /**
     * Busca reservas con filtros aplicados.
     * @param Estados|null $estado Filtro por estado de la reserva.
     * @param string|null $fechaInicio Filtro por fecha de entrada (formato 'Y-m-d').
     * @param string|null $fechaFin Filtro por fecha de salida (formato 'Y-m-d').
     * @param string|null $nombreHuesped Filtro por nombre del huésped (búsqueda parcial).
     * @return array Devuelve un array de objetos Reserva
     */
    public function buscarConFiltros(?Estados $estado = null, ?string $fechaInicio = null, ?string $fechaFin = null, ?string $nombreHuesped = null): array
    {
        $sql    = ReservaSql::BUSCAR_CON_FILTROS;
        $params = [];

        if ($estado !== null) {
            $sql     .= " AND estado = ?";
            $params[] = $estado->getEstado();
        }

        if ($nombreHuesped !== null && trim($nombreHuesped) !== '') {
            $sql     .= " AND nombre_huesped LIKE ?";
            $params[] = '%' . $nombreHuesped . '%';
        }

        if ($fechaInicio !== null && trim((string) $fechaInicio) !== '' && $fechaFin !== null && trim((string) $fechaFin) !== '') {
            $sql     .= " AND fecha_entrada >= ? AND fecha_salida <= ?";
            $params[] = $fechaInicio;
            $params[] = $fechaFin;
        }

        $sql .= " ORDER BY fecha_creacion DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $reservas = [];
        foreach ($resultados as $data) {
            $reservas[] = $this->mapearObjetoReserva($data);
        }

        return $reservas;
    }

    /**
     * Inserta una nueva reserva en la base de datos.
     * @param Reserva $reserva El objeto Reserva a insertar.
     * @return int El ID de la nueva reserva insertada.
     */
    public function insertar(Reserva $reserva): int
    {
        $stmt = $this->db->prepare(ReservaSql::INSERTAR);
        $stmt->execute([
            $reserva->getNombreHuesped(),
            $reserva->getEmailHuesped(),
            $reserva->getNombreAlojamiento(),
            $reserva->getFechaEntrada()->format('Y-m-d'),
            $reserva->getFechaSalida()->format('Y-m-d'),
            $reserva->getEstado()->getEstado(),
            $reserva->getImporte(),
            $reserva->getNotas(),
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Actualiza el estado de una reserva existente en la base de datos.
     * @param int $id El ID de la reserva a actualizar.
     * @param Estados $nuevoEstado El nuevo estado de la reserva.
     * @return bool Devuelve true si la actualización fue exitosa, false en caso contrario
     */
    public function actualizarEstado(int $id, Estados $nuevoEstado): bool
    {
        $stmt = $this->db->prepare(ReservaSql::ACTUALIZAR_ESTADO);
        return $stmt->execute([$nuevoEstado->getEstado(), $id]);
    }

    /**
     * Mapea un array de datos de la base de datos a un objeto Reserva.
     * @param array $data El array de datos de la base de datos.
     * @return Reserva El objeto Reserva mapeado.
     * @throws ValidacionException Si el estado de la reserva es inválido.
     */
    private function mapearObjetoReserva(array $data): Reserva
    {
        $estado = Estados::fromValue((string) ($data['estado'] ?? ''));
        if ($estado === null) {
            throw new ValidacionException("Estado de reserva inválido en la base de datos: " . ($data['estado'] ?? ''));
        }

        return new Reserva(
            id: (int) ($data['id'] ?? 0),
            nombre_huesped: (string) ($data['nombre_huesped'] ?? ''),
            email_huesped: (string) ($data['email_huesped'] ?? ''),
            nombre_alojamiento: (string) ($data['nombre_alojamiento'] ?? ''),
            fecha_entrada: new DateTime((string) ($data['fecha_entrada'] ?? 'now')),
            fecha_salida: new DateTime((string) ($data['fecha_salida'] ?? 'now')),
            estado: $estado,
            importe: (float) ($data['importe'] ?? 0),
            notas: (string) ($data['notas'] ?? '')
        );
    }
}
