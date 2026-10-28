<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\ReservaService;

// Instanciamos el servicio PHP solo para la carga inicial de la página
$reservaService = new ReservaService();

// Configuración de Twig
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');
$twig = new \Twig\Environment($loader);

// Obtenemos la URL exacta que está visitando el usuario
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// ==========================================
// VISTA DE DETALLE (Renderizado servidor inicial)
// ==========================================
if ($uri === '/detalle') {
    $id = $_GET['id'] ?? null;
    
    // Validación de seguridad para el 404
    if (!$id || !is_numeric($id)) {
        header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
        echo $twig->render('error/404.html.twig');
        exit;
    }

    // Pedimos los datos al backend
    $reserva = $reservaService->obtenerDetalleReserva($id);
    $reservaData = $reserva['data'] ?? $reserva;

    // VALIDACIÓN 404: Si la reserva no existe o la API da error
    if (empty($reservaData) || isset($reservaData['error'])) {
        header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
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
// LISTADO PRINCIPAL (HOME - Renderizado servidor)
// ==========================================
if ($uri === '/' || $uri === '/index.php') {
    
    // Hacemos una carga inicial con todas las reservas para que Twig las pinte
    // (A partir de aquí, será jQuery quien filtre y actualice la tabla)
    $todasLasReservas = $reservaService->obtenerReservas();
    
    echo $twig->render('reservas/index.html.twig', [
        'reservas' => $todasLasReservas['data'] ?? $todasLasReservas
    ]);
    exit;
}

// ==========================================
// CATCH-ALL (Cualquier otra URL que no exista)
// ==========================================
header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
echo $twig->render('error/404.html.twig');
exit;