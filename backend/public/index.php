<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Router/Router.php';
require_once __DIR__ . '/../src/Controllers/ReservaController.php';

$router = new Router();

try {
    $controller = new ReservaController();
} catch (Throwable $e) {
    http_response_code($e instanceof ReservaException ? $e->getCode() : 500);
    echo json_encode([
        "status" => "error",
        "message" => "Error al inicializar la API: " . $e->getMessage(),
    ]);
    exit;
}

$router->get('/api/reservas', [$controller, 'listar']);
$router->get('/api/reservas/detalle', [$controller, 'verDetalle']);
$router->post('/api/reservas/estado', [$controller, 'cambiarEstado']);
$router->post('/api/reservas', function () use ($controller) {
    $controller->crearReserva();
});

// Ejecuta el enrutador según la petición actual
$router->resolver($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
