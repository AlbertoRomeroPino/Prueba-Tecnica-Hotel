<?php
declare(strict_types=1);

header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// 2. Interceptar la petición 'Preflight' (OPTIONS) del navegador
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(); // Cortamos la ejecución aquí para que no pase al Router
}

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
        ? (int)$e->getCode() 
        : 500;

    http_response_code($statusCode);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage() ?: "Error interno del servidor.",
    ]);
}