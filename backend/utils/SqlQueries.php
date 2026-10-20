<?php

declare (strict_types = 1);

class ReservaSql
{
    public const BUSCAR_POR_ID = "SELECT * FROM reserva WHERE id = ?";
    public const BUSCAR_CON_FILTROS = "SELECT * FROM reserva WHERE 1=1";
    public const INSERTAR = "INSERT INTO reserva (nombre_huesped, email_huesped, nombre_alojamiento, fecha_entrada, fecha_salida, estado, importe, notas) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    public const ACTUALIZAR_ESTADO = "UPDATE reserva SET estado = ? WHERE id = ?";
}

class ReservaQueries extends ReservaSql
{
}
