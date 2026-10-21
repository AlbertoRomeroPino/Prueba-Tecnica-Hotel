<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Router/Router.php';
require_once __DIR__ . '/../src/Controllers/ReservaController.php';
require_once __DIR__ . '/../src/Controllers/ReservaEventoController.php';

$router = new Router();

try {
    $controller = new ReservaController();
    $reservaEventoController = new ReservaEventoController();
} catch (Throwable $e) {
    http_response_code($e instanceof ReservaException ? $e->getCode() : 500);
    echo json_encode([
        "status" => "error",
        "message" => $e instanceof ReservaException ? $e->getMessage() : "Error interno del servidor.",
    ]);
    exit;
}

$router->get('/api/reservas', [$controller, 'listar']);

$router->get('/api/reservas/{id}', function ($id) use ($controller) {
    $controller->verDetalle((int) $id);
});

$router->get('/api/reservas/{id}/eventos', function ($id) use ($reservaEventoController) {
    $reservaEventoController->listarPorReserva((int) $id);
});

$router->post('/api/reservas/{id}/estado', function ($id) use ($controller) {
    $controller->cambiarEstado((int) $id);
});
$router->post('/api/reservas', function () use ($controller) {
    $controller->crearReserva();
});

// Ejecuta el enrutador según la petición actual
$router->resolver($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);