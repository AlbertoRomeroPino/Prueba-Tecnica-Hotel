<?php
declare(strict_types=1);

class Router
{
    private array $rutas = [];

    public function get(string $ruta, callable $accion): void
    {
        $this->registrarRuta('GET', $ruta, $accion);
    }

    public function post(string $ruta, callable $accion): void
    {
        $this->registrarRuta('POST', $ruta, $accion);
    }

    private function registrarRuta(string $metodo, string $ruta, callable $accion): void
    {
        // Convertimos rutas como /api/reservas/{id} en expresiones regulares: /^\/api\/reservas\/([^\/]+)$/
        $patron = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $ruta);
        $patron = "#^" . $patron . "$#i";

        $this->rutas[$metodo][$patron] = [
            'accion' => $accion,
            'ruta_original' => $ruta
        ];
    }

    public function resolver(string $urlActual, string $metodoActual): void
    {
        $rutaNormalizada = $this->normalizarRuta($urlActual);
        $metodo = strtoupper($metodoActual);

        if (!isset($this->rutas[$metodo])) {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "Ruta no encontrada"]);
            return;
        }

        // Recorremos las rutas registradas buscando coincidencias con expresiones regulares
        foreach ($this->rutas[$metodo] as $patron => $datos) {
            if (preg_match($patron, $rutaNormalizada, $coincidencias)) {
                // Eliminamos la coincidencia completa de la primera posición, dejando solo los parámetros capturados
                array_shift($coincidencias);

                try {
                    // Ejecutamos la acción pasando los parámetros de la URL (ej. el ID)
                    call_user_func_array($datos['accion'], $coincidencias);
                } catch (Throwable $e) {
                    if (!($e instanceof ReservaException)) {
                        error_log('[Router] ' . $e::class . ': ' . $e->getMessage());
                    }
                    http_response_code($e instanceof ReservaException ? $e->getCode() : 500);
                    echo json_encode([
                        "status" => "error",
                        "message" => $e instanceof ReservaException ? $e->getMessage() : "Error interno del servidor.",
                    ]);
                }
                return;
            }
        }

        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "Ruta no encontrada"]);
    }

    private function normalizarRuta(string $urlActual): string
    {
        $ruta = parse_url($urlActual, PHP_URL_PATH) ?? $urlActual;
        $ruta = urldecode($ruta);

        if (preg_match('#/(?:.+?/)?index\.php(/.*)?$#i', $ruta, $coincidencias)) {
            $ruta = $coincidencias[1] ?? '/';
        }

        if ($ruta === '') {
            $ruta = '/';
        }

        // Mantenemos la barra final opcional normalizando la ruta limpia
        return rtrim($ruta, '/') ?: '/';
    }
}