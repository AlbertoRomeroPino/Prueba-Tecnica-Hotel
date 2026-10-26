<?php
require_once __DIR__ . '/../vendor/autoload.php';

// 1. FORZAR LA CARGA DEL ARCHIVO (Añade esta línea)
require_once __DIR__ . '/../src/Services/ReservaService.php';

use App\Services\ReservaService;

$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');
$twig = new \Twig\Environment($loader);

$reservaService = new ReservaService();
$todasLasReservas = $reservaService->obtenerTodasLasReservas();

$listaFinal = isset($todasLasReservas['data']) ? $todasLasReservas['data'] : $todasLasReservas;

echo $twig->render('reservas/index.html.twig', [
    'reservas' => $listaFinal
]);

