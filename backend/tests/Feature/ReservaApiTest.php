<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ReservaApiTest extends TestCase
{
    private string $baseUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->baseUrl = rtrim((string) (getenv('API_BASE_URL') ?: 'http://localhost/backend/public/index.php'), '/');
    }

    /**
     * 1. Verifica el listado general y el detalle de un registro existente en el seed (ID 1: Carlos García).
     */
    public function testListarYVerDetalleDeReservaExistente(): void
    {
        // Listar todas
        $listResponse = $this->requestJson('GET', '/api/reservas');
        $this->assertSame('success', $listResponse['status'] ?? null);
        $this->assertIsArray($listResponse['data'] ?? null);
        $this->assertNotEmpty($listResponse['data'], 'El listado de reservas no debería estar vacío con el seed cargado.');

        // Ver detalle de la reserva #1 (Carlos García)
        $detailResponse = $this->requestJson('GET', '/api/reservas/1');
        $this->assertSame('success', $detailResponse['status'] ?? null);
        
        $reserva = $detailResponse['data'] ?? $detailResponse;
        $this->assertSame('Carlos García', $reserva['nombre_huesped'] ?? null);
        $this->assertSame('confirmada', $reserva['estado'] ?? null);
    }

    /**
     * 2. Verifica que al omitir el campo opcional "estado", se asigna "pendiente" por defecto
     *    y se genera automáticamente el evento de tipo "creacion".
     */
    public function testCrearReservaUsaEstadoPendientePorDefectoYGeneraEvento(): void
    {
        $uid = str_replace('.', '', uniqid('', true));

        // Omitimos deliberadamente 'estado' y 'notas' (campos opcionales)
        $createResponse = $this->requestJson('POST', '/api/reservas', [
            'nombre_huesped'     => 'Test Default ' . $uid,
            'email_huesped'      => "default+{$uid}@test.com",
            'nombre_alojamiento' => 'Suite Vista Mar',
            'fecha_entrada'      => '2026-11-10',
            'fecha_salida'       => '2026-11-12',
            'importe'            => 350.50,
        ]);

        $this->assertSame('success', $createResponse['status'] ?? null);
        $this->assertIsInt($createResponse['id'] ?? null);
        $reservaId = (int) $createResponse['id'];

        // Comprobar mediante el endpoint de detalle que el estado por defecto es 'pendiente'
        $detalleResponse = $this->requestJson('GET', '/api/reservas/' . $reservaId);
        $reserva = $detalleResponse['data'] ?? $detalleResponse;
        $this->assertSame('pendiente', $reserva['estado'] ?? null, 'El estado por defecto debe ser "pendiente".');

        // Comprobar que se registró el evento de creación
        $eventos = $this->obtenerTiposDeEventos($reservaId);
        $this->assertContains('creacion', $eventos);
    }

    /**
     * 3. Verifica el ciclo de vida de cambio de estado (pendiente -> confirmada -> cancelada)
     *    y que cada transición persiste y registra su evento correspondiente.
     */
    public function testCicloCambioDeEstadoGeneraEventosEnHistorial(): void
    {
        $uid = str_replace('.', '', uniqid('', true));

        $createResponse = $this->requestJson('POST', '/api/reservas', [
            'nombre_huesped'     => 'Test Ciclo ' . $uid,
            'email_huesped'      => "ciclo+{$uid}@test.com",
            'nombre_alojamiento' => 'Hotel QA',
            'fecha_entrada'      => '2026-12-01',
            'fecha_salida'       => '2026-12-05',
            'importe'            => 500.00,
        ]);

        $reservaId = (int) $createResponse['id'];

        // Paso 1: Confirmar reserva
        $confirmResponse = $this->requestJson('POST', "/api/reservas/{$reservaId}/estado", [
            'estado' => 'confirmada',
        ]);
        $this->assertSame('success', $confirmResponse['status'] ?? null);

        // Paso 2: Cancelar reserva
        $cancelResponse = $this->requestJson('POST', "/api/reservas/{$reservaId}/estado", [
            'estado' => 'cancelada',
        ]);
        $this->assertSame('success', $cancelResponse['status'] ?? null);

        // Verificar que el estado final en el detalle es 'cancelada'
        $detalleResponse = $this->requestJson('GET', "/api/reservas/{$reservaId}");
        $reserva = $detalleResponse['data'] ?? $detalleResponse;
        $this->assertSame('cancelada', $reserva['estado'] ?? null);

        // Verificar historial completo de eventos (creacion, confirmacion, cancelacion)
        $eventos = $this->obtenerTiposDeEventos($reservaId);
        $this->assertContains('creacion', $eventos);
        $this->assertContains('confirmacion', $eventos);
        $this->assertContains('cancelacion', $eventos);
    }

    /**
     * 4. Verifica que el filtrado combinado (estado + nombre parcial + rango de fechas)
     *    funciona coherentemente con los datos de prueba.
     */
    public function testFiltrarReservasPorEstadoNombreYFechas(): void
    {
        // En el seed inicial: Ana Gómez tiene estado 'pendiente' del 2026-11-05 al 2026-11-08
        $query = http_build_query([
            'estado'       => 'pendiente',
            'nombre'       => 'ana',
            'fecha_inicio' => '2026-11-01',
            'fecha_fin'    => '2026-11-30',
        ]);

        $response = $this->requestJson('GET', '/api/reservas?' . $query);

        $this->assertSame('success', $response['status'] ?? null);
        $this->assertIsArray($response['data'] ?? null);
        $this->assertNotEmpty($response['data'], 'Debería encontrar al menos la reserva de Ana Gómez.');

        foreach ($response['data'] as $reserva) {
            $this->assertSame('pendiente', $reserva['estado']);
            $this->assertStringContainsStringIgnoringCase('ana', (string) $reserva['nombre_huesped']);
        }
    }

    /**
     * 5. Caso negativo: Verifica que la API rechaza un estado fuera del ENUM ('pendiente', 'confirmada', 'cancelada')
     *    o una creación sin campos obligatorios.
     */
    public function testValidacionRechazaDatosInvalidos(): void
    {
        // Intentar cambiar la reserva #1 a un estado no permitido
        $estadoInvalidoResponse = $this->requestJson('POST', '/api/reservas/1/estado', [
            'estado' => 'estado_inexistente',
        ]);
        $this->assertNotSame(
            'success',
            $estadoInvalidoResponse['status'] ?? null,
            'La API no debe permitir estados fuera de: pendiente, confirmada, cancelada.'
        );

        // Intentar crear una reserva sin campos obligatorios (falta email_huesped, fechas, importe...)
        $creacionIncompletaResponse = $this->requestJson('POST', '/api/reservas', [
            'nombre_huesped' => 'Usuario Incompleto',
        ]);
        $this->assertNotSame(
            'success',
            $creacionIncompletaResponse['status'] ?? null,
            'La API debe fallar si faltan campos obligatorios al crear una reserva.'
        );
    }

    /**
     * Helper para extraer y normalizar (en minúsculas) los tipos de eventos de una reserva.
     *
     * @return list<string>
     */
    private function obtenerTiposDeEventos(int $reservaId): array
    {
        $eventosResponse = $this->requestJson('GET', "/api/reservas/{$reservaId}/eventos");
        $this->assertSame('success', $eventosResponse['status'] ?? null);
        $this->assertIsArray($eventosResponse['data'] ?? null);

        return array_map(
            static fn(array $evento): string => strtolower((string) ($evento['tipo'] ?? '')),
            $eventosResponse['data']
        );
    }

    /**
     * @param array<string, mixed>|null $body
     * @return array<string, mixed>
     */
    private function requestJson(string $method, string $path, ?array $body = null): array
    {
        $url = $this->baseUrl . $path;

        $parts = [
            'curl',
            '-s',
            '-X',
            escapeshellarg($method),
            escapeshellarg($url),
            '-H',
            escapeshellarg('Content-Type: application/json'),
        ];

        if ($body !== null) {
            $json = json_encode($body, JSON_UNESCAPED_UNICODE);
            self::assertNotFalse($json, 'No se pudo serializar el body JSON para la petición de test.');
            $parts[] = '-d';
            $parts[] = escapeshellarg($json);
        }

        $command = implode(' ', $parts);
        $output = shell_exec($command);

        self::assertIsString($output, 'No se recibió respuesta del endpoint API.');

        $decoded = json_decode($output, true);
        self::assertIsArray($decoded, 'La API no devolvió un JSON válido. Respuesta cruda: ' . $output);

        return $decoded;
    }
}