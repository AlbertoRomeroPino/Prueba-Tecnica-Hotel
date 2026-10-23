
# Documentación del Backend: API de Reservas

Esta documentación detalla los endpoints, modelos de datos y la cobertura de tests para el sistema backend de gestión de reservas de alojamiento.

---

## 1. Configuración Base

Todas las peticiones a la API deben realizarse sobre la siguiente ruta base:

```text
Base URL: http://localhost/backend/public/index.php/api
```

**Cabeceras Globales Recomendadas:**
Para los métodos POST/PUT que envían datos, se debe incluir:

```http
Content-Type: application/json
```

---

## 2. Modelos de Datos

El sistema se compone principalmente de dos entidades para gestionar el ciclo de vida de una reserva y su trazabilidad.

### `reserva`

Tabla principal de almacenamiento de reservas.

* `id`
* `nombre_huesped`
* `email_huesped`
* `nombre_alojamiento`
* `fecha_entrada`
* `fecha_salida`
* `estado` (Valores permitidos: `pendiente` | `confirmada` | `cancelada`)
* `importe`
* `notas`
* `fecha_creacion`

### `reserva_evento` (Historial)

Tabla de trazabilidad. Cada vez que se crea o cambia de estado una reserva, se añade un registro automáticamente.

* `id`
* `reserva_id`
* `tipo` (Ej: `creacion`, `confirmacion`, `cancelacion`, `modificacion`)
* `descripcion`
* `fecha_creacion`

---

## 3. Endpoints de la API

### 3.1. Listar y Filtrar Reservas

Recupera un listado de las reservas. Permite aplicar múltiples filtros mediante parámetros de consulta (Query Strings).

* **Método:** `GET`
* **Ruta:** `/reservas`

**Ejemplos de filtrado:**

* **Listar todo:** `/reservas`
* **Por estado:** `/reservas?estado=confirmada`
* **Por nombre (parcial):** `/reservas?nombre=juan`
* **Por rango de fechas:** `/reservas?fecha_inicio=2026-10-01&fecha_fin=2026-10-31`
* **Filtros combinados:** `/reservas?estado=pendiente&nombre=ana&fecha_inicio=2026-10-01&fecha_fin=2026-10-31`

### 3.2. Crear Reserva

Registra una nueva reserva en el sistema. Al crearse, se registra automáticamente un evento de tipo `"creacion"` en el historial.

* **Método:** `POST`
* **Ruta:** `/reservas`

**Cuerpo de la petición (JSON):**

```json
{
	"nombre_huesped": "Juan Perez",
	"email_huesped": "juan@email.com",
	"nombre_alojamiento": "Hotel Centro",
	"fecha_entrada": "2026-10-21",
	"fecha_salida": "2026-10-25",
	"importe": 450.75,
	"estado": "pendiente",
	"notas": "Llegada tarde"
}
```

* **Campos obligatorios:** `nombre_huesped`, `email_huesped`, `nombre_alojamiento`, `fecha_entrada`, `fecha_salida`, `importe`.
* **Campos opcionales:** `estado` (por defecto usa `"pendiente"`), `notas`.

### 3.3. Ver Detalle de una Reserva

Recupera la información completa de una reserva específica.

* **Método:** `GET`
* **Ruta:** `/reservas/{id}`
* **Ejemplo:** `/reservas/1`

### 3.4. Cambiar Estado de una Reserva

Actualiza el estado de una reserva. Al ejecutarse con éxito, registra automáticamente un evento en el historial (tipo "confirmacion", "cancelacion" o "modificacion").

* **Método:** `POST`
* **Ruta:** `/reservas/{id}/estado`
* **Ejemplo:** `/reservas/1/estado`

**Cuerpo de la petición (JSON):**

```json
{
	"estado": "confirmada"
}
```

* **Estados permitidos:** `pendiente`, `confirmada`, `cancelada`.

### 3.5. Historial de Eventos de una Reserva

Recupera la trazabilidad y todos los cambios de estado asociados a una reserva.

* **Método:** `GET`
* **Ruta:** `/reservas/{id}/eventos`
* **Ejemplo:** `/reservas/1/eventos`

---

## 4. Tests y QA (Aseguramiento de Calidad)

El backend cuenta con una suite de pruebas automatizadas (PHPUnit) que garantizan la solidez y el correcto funcionamiento de las reglas de negocio.

**Entorno de Ejecución:**

* **PHPUnit:** 11.5.57
* **PHP:** 8.3.6
* **Resultado:** 5 / 5 (100%) - Time: 00:00.177, Memory: 8.00 MB

### Resumen de la Suite de Pruebas


| Nombre del Test                                             | Qué valida realmente                                                                                                                                                                                           |
| :------------------------------------------------------------ | :---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `testListarYVerDetalleDeReservaExistente`                   | Cubre los endpoints de listado y detalle. Asegura que la conexión a base de datos y la serialización JSON funcionan con los datos semilla.                                                                    |
| `testCrearReservaUsaEstadoPendientePorDefectoYGeneraEvento` | Cubre endpoints de creación, detalle y eventos. Valida la regla de negocio implícita: si no se envía el campo opcional `estado`, se asume "pendiente" y se crea el evento.                                   |
| `testCicloCambioDeEstadoGeneraEventosEnHistorial`           | Cubre el endpoint de cambio de estado y el historial. Prueba la máquina de estados completa (`pendiente` $\rightarrow$ `confirmada` $\rightarrow$ `cancelada`) y su correcta trazabilidad en `reserva_evento`. |
| `testFiltrarReservasPorEstadoNombreYFechas`                 | Cubre el endpoint de listado con filtros. Combina todos los parámetros de búsqueda en una sola llamada contra datos conocidos.                                                                                |
| `testValidacionRechazaDatosInvalidos`                       | Demuestra criterio de QA comprobando que la API protege la integridad de los datos frente a entradas erróneas (Sad Path).                                                                                      |
