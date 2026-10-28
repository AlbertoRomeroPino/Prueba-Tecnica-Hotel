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

    // 1. Carga inicial del listado de reservas (Para index.html.twig)
    public function obtenerReservas(array $filtros = [])
    {
        try {
            $response = $this->client->request('GET', 'reservas', [
                'query' => $filtros
            ]);
            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            return [];
        }
    }

    // 2. Carga inicial del detalle de la reserva (Para detalle.html.twig)
    public function obtenerDetalleReserva($id)
    {
        try {
            $response = $this->client->request('GET', "reservas/{$id}");
            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            return null;
        }
    }

    // 3. Carga inicial del historial de eventos (Para detalle.html.twig)
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