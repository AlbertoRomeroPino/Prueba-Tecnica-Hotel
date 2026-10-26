<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ReservaService
{
    private $client;

    public function __construct()
    {
        // Configuramos Guzzle con la URL base de tu backend
        $this->client = new Client([
            'base_uri' => 'http://localhost/backend/public/index.php/api/',
            'timeout'  => 5.0,
        ]);
    }

    public function obtenerTodasLasReservas()
    {
        try {
            // Hace un GET a http://localhost/backend/.../api/reservas
            $response = $this->client->request('GET', 'reservas');
            
            // Decodifica el JSON a un array de PHP
            return json_decode($response->getBody()->getContents(), true);
            
        } catch (RequestException $e) {
            // Si el backend está caído o da error, devolvemos un array vacío
            return [];
        }
    }
}