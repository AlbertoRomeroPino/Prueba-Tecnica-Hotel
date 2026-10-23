<?php

declare(strict_types=1);

use Exception;

class Router
{
    private array $rutas = [];

    /**
     * Registra una ruta por método GET.
     */
    public function get(string $ruta, callable $accion): void
    {
        $this->registrarRuta('GET', $ruta, $accion);
    }

    /**
     * Registra una ruta por método POST.
     */
    public function post(string $ruta, callable $accion): void
    {
        $this->registrarRuta('POST', $ruta, $accion);
    }

    /**
     * Convierte la ruta con parámetros en una expresión regular y la almacena.
     */
    private function registrarRuta(string $metodo, string $ruta, callable $accion): void
    {
        // Convierte /api/reservas/{id} en la regex #^/api/reservas/([^/]+)$#i
        $patron = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $ruta);
        $patron = "#^" . $patron . "$#i";

        $this->rutas[$metodo][$patron] = $accion;
    }

    /**
     * Resuelve la petición actual ejecutando el controlador/callback correspondiente.
     *
     * @throws Exception Si la ruta o el método no existen (404 / 405).
     */
    public function resolver(string $urlActual, string $metodoActual): void
    {
        $rutaNormalizada = $this->normalizarRuta($urlActual);
        $metodo = strtoupper($metodoActual);

        if (!isset($this->rutas[$metodo])) {
            throw new Exception("Método HTTP no soportado para esta ruta.", 405);
        }

        foreach ($this->rutas[$metodo] as $patron => $accion) {
            if (preg_match($patron, $rutaNormalizada, $coincidencias)) {
                array_shift($coincidencias);
                call_user_func_array($accion, $coincidencias);
                return;
            }
        }

        throw new Exception("Ruta no encontrada.", 404);
    }

    /**
     * Limpia la URL entrante para el motor de expresiones regulares.
     */
    private function normalizarRuta(string $urlActual): string
    {
        $ruta = parse_url($urlActual, PHP_URL_PATH) ?? '/';
        $ruta = urldecode($ruta);

        // Limpieza de subdirectorios o llamadas directas a index.php
        if (preg_match('#/(?:.+?/)?index\.php(/.*)?$#i', $ruta, $coincidencias)) {
            $ruta = $coincidencias[1] ?? '/';
        }

        return rtrim($ruta, '/') ?: '/';
    }
}