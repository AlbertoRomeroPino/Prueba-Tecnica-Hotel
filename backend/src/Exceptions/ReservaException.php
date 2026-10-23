<?php

declare(strict_types=1);

/**
 * Excepción base del sistema de reservas (HTTP 500 por defecto).
 */
class ReservaException extends Exception
{
    public function __construct(
        string $message = "Error en el sistema de reservas.",
        int $code = 500,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}

/**
 * Excepción para datos de entrada no válidos o reglas de negocio incumplidas (HTTP 400).
 */
class ValidacionException extends ReservaException
{
    public function __construct(
        string $message = "Los datos proporcionados no son válidos.",
        int $code = 400,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}

/**
 * Excepción para cuando no se encuentra un registro en la base de datos (HTTP 404).
 */
class ReservaNotFoundException extends ReservaException
{
    public function __construct(
        string $message = "La reserva solicitada no existe.",
        int $code = 404,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}