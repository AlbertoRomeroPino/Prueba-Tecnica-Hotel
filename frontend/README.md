# Panel de Gestión de Reservas - Frontend

Cliente web híbrido para la gestión de reservas hoteleras, combinando renderizado inicial en servidor (SSR) con Twig y reactividad en el cliente mediante JavaScript y AJAX.

## Tecnologías utilizadas

- **Servidor / Enrutado:** PHP 8.x
- **Motor de plantillas:** Twig 3.x
- **Cliente HTTP (Servidor):** Guzzle
- **Cliente HTTP (Navegador):** JavaScript (ES6+) con jQuery (`$.ajax`)
- **Estilos:** Tailwind CSS

---

## Estructura del proyecto

El código está organizado separando las responsabilidades del renderizado inicial en PHP y la lógica interactiva del navegador:

```text
/
├── public/
│   ├── js/
│   │   ├── ReservaService.js  # Clase JS (jQuery) para consumo directo de la API
│   │   └── reservas.js        # Lógica de UI (Event Delegation, manipulación del DOM)
│   └── index.php              # Enrutador Front-Controller (punto de entrada único)
├── src/
│   └── Services/
│       └── ReservaService.php # Cliente PHP con Guzzle (lectura inicial para Twig)
├── templates/
│   ├── error/
│   │   └── 404.html.twig      # Página 404 personalizada
│   ├── layout/
│   │   └── base.html.twig     # Plantilla base maestra
│   └── reservas/
│       ├── detalle.html.twig  # Vista de detalle e historial
│       └── index.html.twig    # Vista principal (listado, filtros y formulario)
├── composer.json          
└── README.md
```

---

## Instalación y puesta en marcha

1. **Instalar dependencias:**
   Asegúrate de tener Composer instalado y ejecuta en la raíz del proyecto:

   ```bash
   composer install
   ```
2. **Arrancar el servidor de desarrollo:**
   Inicia el servidor interno de PHP apuntando al directorio `public` y al enrutador:

   ```bash
   php -S localhost:8000 -t public public/index.php
   ```

> **Nota:** El backend de la API debe estar corriendo en paralelo con las reglas CORS configuradas (por ejemplo, en `http://localhost/backend/public/index.php/api/`).

---

## Arquitectura y decisiones técnicas

Para cumplir con los dos bloques principales de la prueba —que pedían tanto vistas renderizadas en servidor con Twig como manipulación del DOM sin recargar la página mediante JS—, he optado por un enfoque híbrido:

### 1. Renderizado inicial en servidor (PHP + Twig)

La primera carga de cada ruta (`/` y `/detalle`) la gestiona PHP. El punto de entrada `public/index.php` llama a `ReservaService.php` (usando Guzzle) para hacer el `GET` inicial a la API y pasarle los datos a Twig. Así el navegador recibe el HTML ya montado desde el primer segundo, cumpliendo el requisito de SSR y evitando pantallas en blanco mientras carga el JS.

### 2. Interactividad en cliente (JS + jQuery)

Una vez cargado el HTML inicial, el control pasa a `ReservaService.js` y `reservas.js`. Todas las operaciones dinámicas (filtrar la tabla, crear reservas o cambiar estados) se hacen directamente contra la API desde el navegador con `$.ajax`.

De esta forma, `ReservaService.php` se mantiene limpio solo para lectura (`GET`), y nos ahorramos usar el servidor de frontend como pasarela innecesaria para las peticiones `POST` y `PUT`.

### 3. Enrutamiento centralizado (Front-Controller)

Todo el tráfico entra por `public/index.php`. Al ser un cliente ligero, no tenía sentido montar una estructura compleja de controladores. Centralizarlo aquí me ha permitido validar de forma sencilla los parámetros de entrada (como interceptar IDs no válidos en `/detalle?id=abc`) y redirigir cualquier ruta rota a una página 404 personalizada con temática de hotel ("Habitación no encontrada").

### 4. Seguridad (Autoescaping en Twig)

No se ha utilizado el filtro `|raw` en ninguna plantilla. Todo el contenido dinámico que viene de la API (nombres de clientes, notas, estados) pasa por el escapado automático de Twig para evitar inyecciones XSS.

### 5. Delegación de eventos en el DOM

Como la tabla de reservas se vuelve a pintar por completo en el DOM cada vez que se aplica un filtro por AJAX, no he puesto ningún `onclick` ni `onchange` en el HTML. En su lugar, he colocado un único *listener* en el contenedor de la tabla (`#tabla-reservas`) que captura los eventos de los `<select>` hijos. Así el código queda desacoplado del HTML y los elementos creados dinámicamente siguen funcionando sin tener que reasignar eventos.

---

## Checklist de requisitos implementados

Resumen de cómo se ha cubierto cada punto pedido en el enunciado para la parte de vistas y frontend:

### Vistas — Twig

- [X]  **Renderizado de al menos dos vistas en servidor:** Implementadas `index.html.twig` (listado principal) y `detalle.html.twig` (ficha de la reserva).
- [X]  **Listado de reservas (tabla + filtros):** Incluido en la vista principal.
- [X]  **Detalle + historial de eventos:** Accesible desde la ruta `/detalle`, mostrando los datos de la reserva y su registro de cambios.
- [X]  **Plantilla base y herencia:** Creada `layout/base.html.twig` con la estructura común (`<head>`, estilos, scripts). Las vistas heredan con `{% extends %}` e inyectan su contenido en `{% block content %}`.
- [X]  **Autoescaping y justificación de `|raw`:** Mantengo el autoescaping nativo en todas las vistas; se ha prescindido de `|raw` por seguridad frente a XSS.
- [X]  **Filtros y formato en plantilla:** Uso de filtros nativos de Twig directamente en las vistas para formatear datos de salida (ej. `{{ reserva.importe|number_format(2, ',', '.') }} €` y formato de fechas).

### Frontend — HTML + JS

- [X]  **Filtrar el listado contra la API sin recargar:** La función `aplicarFiltros()` recoge los inputs, consulta la API por AJAX y actualiza el cuerpo de la tabla en tiempo real.
- [X]  **Formulario de creación (validación cliente + servidor):** Validación en cliente con atributos HTML (`required`) y comprobación en JS para asegurar que la fecha de salida sea posterior a la de entrada. Si la API devuelve un error de negocio, se captura y se muestra el mensaje exacto del servidor (`result.mensaje`).
- [X]  **Cancelar reserva con confirmación:** Al cambiar el desplegable de estado a *"cancelada"*, se pide confirmación mediante `confirm('¿Seguro que deseas cancelar esta reserva?')` antes de lanzar el `PUT`.
- [X]  **Feedback visual (carga y respuestas):** La función `mostrarCargando()` inyecta un spinner SVG mientras se resuelven las peticiones AJAX, acompañado de alertas con el resultado de la operación.
- [X]  **HTML semántico y JS organizado:** Estructura HTML limpia sin lógica inline, gestionando toda la interactividad desde archivos JS externos mediante delegación de eventos.
- [X]  **Manejo de errores de red (400 / 500):** Las promesas de jQuery cuentan con su bloque `.catch()`. Si la API cae o devuelve un error de red, se avisa al usuario tanto por alerta como inyectando un mensaje de error en la propia tabla.
- [X]  **Diseño responsive:** Maquetado con Tailwind CSS, adaptando los filtros a móvil (`flex-col md:flex-row`) y añadiendo `overflow-x-auto` en la tabla para permitir scroll horizontal en pantallas pequeñas sin romper el layout.
