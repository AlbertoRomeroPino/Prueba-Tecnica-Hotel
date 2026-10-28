<?php

declare(strict_types=1);

enum TipoReservaEvento: string
{
    case CREACION = 'creacion';
    case MODIFICACION = 'modificacion';
    case CONFIRMACION = 'confirmacion';
    case NOTIFICACION = 'notificacion';
    case CANCELACION = 'cancelacion';

    public static function fromValue(string $valor): ?self
    {
        return match (strtolower(trim($valor))) {
            'creacion' => self::CREACION,
            'modificacion' => self::MODIFICACION,
            'confirmacion' => self::CONFIRMACION,
            'notificacion' => self::NOTIFICACION,
            'cancelacion' => self::CANCELACION,
            default => null,
        };
    }
}


?>