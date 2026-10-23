<?php
declare(strict_types=1);

$reservaController = new ReservaController();
$reservaEventoController = new ReservaEventoController();

// Listar todas las reservas (y manejar los filtros por GET)
$router->get('/api/reservas', [$reservaController, 'listar']);

// Crear una nueva reserva
$router->post('/api/reservas', function () use ($reservaController) {
    $reservaController->crearReserva();
});

// Ver detalle de una reserva específica
$router->get('/api/reservas/{id}', function (string $id) use ($reservaController) {
    $reservaController->verDetalle((int) $id);
});

// Cambiar el estado de una reserva
$router->post('/api/reservas/{id}/estado', function (string $id) use ($reservaController) {
    $reservaController->cambiarEstado((int) $id);
});

// Listar el historial de eventos de una reserva específica
$router->get('/api/reservas/{id}/eventos', function (string $id) use ($reservaEventoController) {
    $reservaEventoController->listarPorReserva((int) $id);
});