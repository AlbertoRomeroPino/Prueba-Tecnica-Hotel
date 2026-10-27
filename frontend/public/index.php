<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\ReservaService;

$reservaService = new ReservaService();
$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true);

    if ($action === 'crear') {
        $respuesta = $reservaService->crearReserva($input);
        
        if (isset($respuesta['error'])) {
            http_response_code(400);
        }
        echo json_encode($respuesta);
        exit;
    }

    if ($action === 'cancelar') {
        $id = $input['id'] ?? 0;
        $respuesta = $reservaService->cambiarEstado($id, 'cancelada');
        echo json_encode($respuesta);
        exit;
    }
}

$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');
$twig = new \Twig\Environment($loader);

if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    $filtros = !empty($_GET['estado']) ? ['estado' => $_GET['estado']] : [];
    $todasLasReservas = $reservaService->obtenerReservas($filtros);
    
    header('Content-Type: application/json');
    echo json_encode($todasLasReservas['data'] ?? $todasLasReservas);
    exit;
}

$todasLasReservas = $reservaService->obtenerReservas();
echo $twig->render('reservas/index.html.twig', [
    'reservas' => $todasLasReservas['data'] ?? $todasLasReservas
]);