<?php

enum Estados {
    case PENDIENTE;
    case CONFIRMADA;
    case CANCELADA;

    public static function fromValue(string $valor): ?self
    {
        return match (strtolower(trim($valor))) {
            'pendiente'  => self::PENDIENTE,
            'confirmada' => self::CONFIRMADA,
            'cancelada'  => self::CANCELADA,
            default      => null,
        };
    }

    public function getEstado(): string
    {
        return match ($this) {
            Estados::PENDIENTE  => 'pendiente',
            Estados::CONFIRMADA => 'confirmada',
            Estados::CANCELADA  => 'cancelada',
        };
    }
}
