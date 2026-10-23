<?php
declare(strict_types=1);

header("Content-Type: application/json; charset=UTF-8");


require_once __DIR__ . '/../src/Router/Router.php';
require_once __DIR__ . '/../src/Controllers/ReservaController.php';
require_once __DIR__ . '/../src/Controllers/ReservaEventoController.php';

try {
    $router = new Router();

    require_once __DIR__ . '/../routes/api.php';

    $router->resolver($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);

} catch (Throwable $e) {
    $statusCode = (property_exists($e, 'getCode') && $e->getCode() >= 400 && $e->getCode() < 600) 
        ? $e->getCode() 
        : 500;

    http_response_code($statusCode);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage() ?: "Error interno del servidor.",
    ]);
}