<?php
class Router
{
    private array $rutas = [];

    public function get(string $ruta, callable $accion): void
    {
        $this->rutas['GET'][$ruta] = $accion;
    }

    public function post(string $ruta, callable $accion): void
    {
        $this->rutas['POST'][$ruta] = $accion;
    }

    public function resolver(string $urlActual, string $metodoActual): void
    {
        $rutaNormalizada = $this->normalizarRuta($urlActual);
        $metodo = strtoupper($metodoActual);

        if (isset($this->rutas[$metodo][$rutaNormalizada])) {
            try {
                call_user_func($this->rutas[$metodo][$rutaNormalizada]);
            } catch (Throwable $e) {
                http_response_code($e instanceof ReservaException ? $e->getCode() : 500);
                echo json_encode([
                    "status" => "error",
                    "message" => $e->getMessage(),
                ]);
            }
            return;
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

        return rtrim($ruta, '/');
    }
}
