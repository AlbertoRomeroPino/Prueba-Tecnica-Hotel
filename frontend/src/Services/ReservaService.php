<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ReservaService
{
    private $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'http://localhost/backend/public/index.php/api/',
            'timeout'  => 5.0,
        ]);
    }

    // 1 y 6. Listar y Filtrar reservas
    public function obtenerReservas(array $filtros = [])
    {
        try {
            // Guzzle convierte automáticamente el array $filtros en parámetros URL (?estado=...&nombre=...)
            $response = $this->client->request('GET', 'reservas', [
                'query' => $filtros
            ]);
            
            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            return [];
        }
    }

    // 2. Crear una nueva reserva
    public function crearReserva(array $datos)
    {
        try {
            $response = $this->client->request('POST', 'reservas', [
                'json' => $datos
            ]);
            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            return ['error' => true, 'mensaje' => $e->getMessage()];
        }
    }

    // 3. Ver detalle de una reserva específica
    public function obtenerDetalleReserva($id)
    {
        try {
            $response = $this->client->request('GET', "reservas/{$id}");
            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            return null;
        }
    }

    // 4. Cambiar el estado de la reserva
    public function cambiarEstado($id, $estado)
    {
        try {
            $response = $this->client->request('POST', "reservas/{$id}/estado", [
                'json' => ['estado' => $estado]
            ]);
            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            return ['error' => true, 'mensaje' => $e->getMessage()];
        }
    }

    // 5. Historial de eventos de una reserva
    public function obtenerEventos($id)
    {
        try {
            $response = $this->client->request('GET', "reservas/{$id}/eventos");
            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            return [];
        }
    }
}