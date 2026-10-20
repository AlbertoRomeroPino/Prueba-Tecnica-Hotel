<?php

declare(strict_types=1);

enum TipoReservaEvento: string
{
    case CREACION = 'creacion';
    case MODIFICACION = 'modificacion';
    case NOTIFICACION = 'notificacion';
    case PAGO = 'pago';
    case CANCELACION = 'cancelacion';
}


?>