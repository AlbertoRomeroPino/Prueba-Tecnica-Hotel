<?php

declare (strict_types = 1);

class ReservaSql
{
    public const BUSCAR_POR_ID = "SELECT * FROM reserva WHERE id = ?";
    public const BUSCAR_CON_FILTROS = "SELECT * FROM reserva WHERE 1=1";
    public const INSERTAR = "INSERT INTO reserva (nombre_huesped, email_huesped, nombre_alojamiento, fecha_entrada, fecha_salida, estado, importe, notas) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    public const ACTUALIZAR_ESTADO = "UPDATE reserva SET estado = ? WHERE id = ?";
}

class ReservaEventoSql
{
    public const INSERTAR = "INSERT INTO reserva_evento (reserva_id, tipo, descripcion) VALUES (?, ?, ?)";
    public const BUSCAR_POR_RESERVA_ID = "
        SELECT
            re.id AS evento_id,
            re.reserva_id,
            re.tipo AS evento_tipo,
            re.descripcion AS evento_descripcion,
            re.fecha_creacion AS evento_fecha,
            r.id,
            r.nombre_huesped,
            r.email_huesped,
            r.nombre_alojamiento,
            r.fecha_entrada,
            r.fecha_salida,
            r.estado,
            r.importe,
            r.notas
        FROM reserva_evento re
        INNER JOIN reserva r ON r.id = re.reserva_id
        WHERE re.reserva_id = ?
        ORDER BY re.fecha_creacion DESC, re.id DESC
    ";
}

class ReservaQueries extends ReservaSql
{
}
