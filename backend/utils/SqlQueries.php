<?php

declare (strict_types = 1);

require_once __DIR__ . '/../Entities/Reserva.php';

class ReservaSql
{
    // Consulta para buscar por ID
    public const BUSCAR_POR_ID = "SELECT * FROM reserva WHERE id = ?";

    // Consulta base para los filtros combinados (usando WHERE 1=1 para añadir AND dinámicamente)
    public const BUSCAR_CON_FILTROS = "SELECT * FROM reserva WHERE 1=1";

    // Consulta para insertar una nueva reserva
    public const INSERTAR = "INSERT INTO reserva (nombre_huesped, email_huesped, nombre_alojamiento, fecha_entrada, fecha_salida, estado, importe, notas) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    // Consulta para actualizar el estado por su ID
    public const ACTUALIZAR_ESTADO = "UPDATE reserva SET estado = ? WHERE id = ?";
}
