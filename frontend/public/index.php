<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\ReservaService;

$reservaService = new ReservaService();
$action = $_GET['action'] ?? '';

// CAPTURAR PETICIONES POST
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

    if ($action === 'cambiar_estado') {
        $id = $input['id'] ?? 0;
        $nuevoEstado = $input['estado'] ?? '';
        
        $respuesta = $reservaService->cambiarEstado($id, $nuevoEstado);
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


// Configuración de Twig
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');
$twig = new \Twig\Environment($loader);

// Obtenemos la URL exacta que está visitando el usuario
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// ==========================================
// VISTA DE DETALLE
// ==========================================
if ($uri === '/detalle') {
    $id = $_GET['id'] ?? 0;
    
    // Pedimos los datos al backend
    $reserva = $reservaService->obtenerDetalleReserva($id);
    $reservaData = $reserva['data'] ?? $reserva;

    // VALIDACIÓN 404: Si la reserva no existe, el ID es inválido o la API da error
    if (empty($reservaData) || isset($reservaData['error'])) {
        http_response_code(404);
        echo $twig->render('error/404.html.twig');
        exit;
    }

    // Si existe, cargamos los eventos y pintamos la vista normal
    $eventos = $reservaService->obtenerEventos($id);
    echo $twig->render('reservas/detalle.html.twig', [
        'reserva' => $reservaData,
        'eventos' => $eventos['data'] ?? $eventos
    ]);
    exit;
}

// ==========================================
// LISTADO PRINCIPAL (HOME)
// ==========================================
if ($uri === '/' || $uri === '/index.php') {
    $filtrosValidos = ['estado', 'nombre', 'fecha_inicio', 'fecha_fin'];
    $filtrosAplicados = [];
    foreach ($filtrosValidos as $filtro) {
        if (!empty($_GET[$filtro])) {
            $filtrosAplicados[$filtro] = $_GET[$filtro];
        }
    }

    // Petición AJAX desde JavaScript para filtrar
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        $todasLasReservas = $reservaService->obtenerReservas($filtrosAplicados);
        header('Content-Type: application/json');
        echo json_encode($todasLasReservas['data'] ?? $todasLasReservas);
        exit;
    }

    // Carga inicial normal en el navegador
    $todasLasReservas = $reservaService->obtenerReservas($filtrosAplicados);
    echo $twig->render('reservas/index.html.twig', [
        'reservas' => $todasLasReservas['data'] ?? $todasLasReservas
    ]);
    exit;
}

// ==========================================
// CATCH-ALL (Cualquier otra URL que no exista)
// ==========================================
http_response_code(404);
echo $twig->render('error/404.html.twig');
exit;