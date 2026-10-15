<?php
// public/index.php

// Este es el ÚNICO require que verás en todo el proyecto
require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\ReservaController;

// A partir de aquí, PHP ya sabe encontrar cualquier clase de "App\"
$controller = new ReservaController();