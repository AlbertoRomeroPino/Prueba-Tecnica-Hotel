<?php

enum Estados
{
    case PENDIENTE;
    case CONFIRMADA;
    case CANCELADA;

    public function getEstado(): string { 
        return match($this) {
            Estados::PENDIENTE => 'pendiente',
            Estados::CONFIRMADA => 'confirmada',
            Estados::CANCELADA => 'cancelada',
        };
    }
}


?>