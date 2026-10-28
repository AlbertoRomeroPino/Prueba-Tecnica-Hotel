# Prueba-Tecnica-Hotel

## **Cómo levantarlo en local** : pasos concretos (crear BD, configurar conexión, arrancar).

### Requisitos Previos

Antes de comenzar, asegúrate de tener instalado en tu equipo:

* **[XAMPP](https://www.apachefriends.org/es/index.html)** (incluye Apache, MySQL/MariaDB y PHP).
* **PHP CLI** (añadido a las variables de entorno de tu sistema para poder ejecutar comandos `php` desde la terminal).
* **Git** para clonar el repositorio.

### Guía de Instalación y Despliegue

#### 1. Clonar el repositorio

Abre tu terminal y clona el proyecto en tu máquina local:

```bash
git clone https://github.com/AlbertoRomeroPino/Prueba-Tecnica-Hotel.git
cd Prueba-Tecnica-Hotel
```

#### 2. Configuración del Backend y Base de Datos (XAMPP)

El backend y la base de datos están gestionados a través de **XAMPP** para permitir la interpretación del código PHP mediante el servidor Apache y el uso de MySQL.

1. **Mover el Backend a `htdocs`:**
   Copia o mueve la carpeta `backend` del repositorio clonado dentro del directorio público de XAMPP (`htdocs`). La ruta variará según tu sistema operativo:

   * **Windows:** `C:\xampp\htdocs\backend`
   * **Linux:** `/opt/lampp/htdocs/backend`
2. **Iniciar los servicios:**
   Abre el panel de control de XAMPP e inicia los módulos **Apache** y **MySQL** (en Linux puedes usar `sudo /opt/lampp/lampp start`).
3. **Importar la Base de Datos:**
   Accede a `http://localhost/phpmyadmin`, crea la base de datos correspondiente e importa el archivo `.sql` del proyecto. Este se encuentra en backend/database/init.sql

#### 3. Ejecución del Frontend

El frontend funciona de manera independiente y más sencilla utilizando el servidor web interno de PHP:

1. Abre una terminal y entra en la carpeta `frontend` del repositorio clonado:
   ```bash
   cd frontend
   ```
2. Levanta el servidor local apuntando al directorio `public` (ya que el punto de entrada no se encuentra en la raíz):
   ```bash
   php -S localhost:8000 -t public
   ```
3. Abre tu navegador y accede a: **`http://localhost:8000`**

#### ⚠️ Notas de Compatibilidad (Windows vs. Linux)

El proyecto ha sido desarrollado siguiendo estándares compatibles entre sistemas operativos, pero si experimentas algún fallo al levantar el entorno en **Windows** o **Linux**, revisa los siguientes puntos clave:

1. **Ubicación de la carpeta `backend`:**
   Para que los endpoints respondan correctamente en `http://localhost/backend/...`, la carpeta dentro de `htdocs` debe llamarse exactamente **`backend`** (en minúsculas). Si la guardas dentro de una subcarpeta (ej. `htdocs/Prueba-Tecnica-Hotel/backend`), las rutas de la API cambiarán y deberás actualizar la URL base en el frontend.
2. **Sensibilidad a mayúsculas y minúsculas (*Case Sensitivity*):**
   Linux distingue estrictamente entre mayúsculas y minúsculas en las rutas y nombres de archivos, mientras que Windows no. Respeta siempre las rutas en minúsculas tal y como están definidas en la documentación.
3. **Permisos en Linux:**
   Si usas Linux y recibes un error `403 Forbidden` o `500 Internal Server Error`, asegúrate de dar permisos de lectura y ejecución a la carpeta ubicada en `/opt/lampp/htdocs/backend`.

## **Decisiones** : por qué organizaste las clases así, por qué elegiste esa BD.

### Backend

#### 1. Organización de las Clases y Arquitectura

La estructura del proyecto se ha diseñado buscando mantener un código **limpio, modular, escalable y fácil de mantener**, siguiendo el principio de separación de responsabilidades (SoC) y una arquitectura por capas inspirada en el patrón **MVC (Modelo-Vista-Controlador)** adaptado a una API.

* **Punto de entrada único (`public/index.php` y `Router`):** El centro de la aplicación es el archivo `index.php` (patrón *Front Controller*), por donde circulan todas las peticiones hacia los diferentes *endpoints*. En conjunto con la capa de enrutamiento (`routes/api.php` y `src/Router`), se encarga de evaluar la petición y redirigirla al controlador correspondiente.
* **Controladores (`src/Controllers`):** Actúan como intermediarios. Reciben las peticiones HTTP, extraen los datos y llaman al servicio correspondiente, devolviendo finalmente una respuesta adecuada al cliente.
* **Modelos (`src/Model`):** Permiten estructurar y tipar las entidades del dominio (`Reserva`, `Reserva_evento`, `Estados`, etc.). Esto asegura que las variables y los datos estén bien organizados en memoria y no generen conflictos ni inconsistencias con la estructura de la base de datos.
* **Servicios y Repositorios (`src/Services` y `src/Repositories`):** Se ha dividido la lógica en dos capas por motivos de organización y seguridad:
  * **Services:** Contienen la lógica de negocio y las validaciones. Aseguran que la información que entra o sale sea coherente antes de procesarla.
  * **Repositories:** Aíslan el acceso a los datos. Son los únicos que interactúan directamente con la base de datos (apoyándose en `utils/SqlQueries.php`), garantizando que las consultas sean seguras (previniendo inyecciones SQL mediante consultas preparadas).
* **Configuración (`src/Config/Database.php`):** Centraliza las credenciales y la configuración para instanciar la conexión a la base de datos mediante **PDO**, facilitando la reutilización de la conexión en toda la aplicación.
* **Gestión de Excepciones (`src/Exceptions/ReservaException.php`):** Se ha creado una clase personalizada para capturar y gestionar los errores de forma centralizada.
  * *Propuesta de mejora a futuro:* Implementar un sistema de *logging* que almacene automáticamente estas excepciones en una carpeta dedicada (generando archivos de texto con el formato `nombre-fecha.log`). Esto permitiría auditar los fallos fácilmente y mejorar la estabilidad de la aplicación de forma continua.

---

#### 2. Elección de la Base de Datos (MySQL)

Se ha optado por utilizar **MySQL** como sistema gestor de base de datos relacional por los siguientes motivos:

1. **Integración con el entorno de desarrollo:** Viene integrada por defecto en el paquete **XAMPP**, lo que agiliza enormemente la configuración inicial del entorno local y el despliegue rápido mediante el script `database/init.sql`.
2. **Curva de aprendizaje y familiaridad:** Al ser una tecnología con la que ya tenía experiencia previa, me ha permitido centrarme en la arquitectura del código y la lógica de negocio sin perder tiempo en problemas de configuración del motor de base de datos.
3. **Abstracción mediante PDO:** Gracias al uso de la extensión **PDO** (PHP Data Objects) en la clase `Database.php` y al aislamiento de las consultas en la capa de `Repositories`, la aplicación no está fuertemente acoplada a este motor. Si en el futuro se decidiera migrar a otra base de datos relacional (como PostgreSQL o SQLite), el cambio sería mínimo y requeriría muy pocas modificaciones en el código.

---

#### 3. Estructura de Directorios del Proyecto

```
.
├── composer.json
├── composer.lock
├── database
│   └── init.sql
├── phpunit.xml
├── public
│   └── index.php
├── README.md
├── routes
│   └── api.php
├── src
│   ├── Config
│   │   └── Database.php
│   ├── Controllers
│   │   ├── ReservaController.php
│   │   └── ReservaEventoController.php
│   ├── Exceptions
│   │   └── ReservaException.php
│   ├── Model
│   │   ├── Estados.php
│   │   ├── Reserva_evento.php
│   │   ├── Reserva.php
│   │   └── TipoReservaEvento.php
│   ├── Repositories
│   │   ├── ReservaEventoRepository.php
│   │   └── ReservaRepository.php
│   ├── Router
│   │   └── Router.php
│   └── Services
│       ├── ReservaEventoService.php
│       └── ReservaService.php
├── tests
│   └── Feature
│       └── ReservaApiTest.php
└── utils
    └── SqlQueries.php
```

## Frontend

### 1. Enfoque y Decisiones de Diseño (Principio KISS)

Al abordar el desarrollo del Frontend e incorporar tecnologías nuevas (como el motor de plantillas **Twig**), se ha optado por seguir una filosofía pragmática basada en el principio **KISS (*Keep It Simple, Stupid*)**.

Dado el proceso de aprendizaje e investigación con estas herramientas, se priorizó construir una arquitectura **lo más sencilla, directa y limpia posible**. Esta decisión ha permitido evitar la sobreingeniería, minimizar la aparición de errores por desconocimiento de la herramienta y garantizar una interfaz funcional, rápida y fácil de comprender.

### 2. Organización de los Archivos

A pesar de mantener una estructura simple, se ha conservado la buena práctica de separar las responsabilidades entre la estructura visual (plantillas) y el comportamiento dinámico (JavaScript):

* **Motor de Plantillas (`templates/` con Twig):** Se ha estructurado la interfaz aprovechando la herencia de plantillas que ofrece Twig para no repetir código HTML:

  * **`layout/base.html.twig`:** Define el esqueleto principal de la página (cabeceras, estilos globales, estructura base) del cual heredan el resto de vistas.
  * **`reservas/` (`index.html.twig` y `detalle.html.twig`):** Contienen exclusivamente las vistas específicas para listar las reservas y ver la información detallada de cada una.
  * **`error/404.html.twig`:** Vista dedicada para gestionar de forma amigable las rutas no encontradas.
* **Lógica de Cliente (`public/js/`):** Para la interactividad en el navegador se ha utilizado JavaScript nativo (Vanilla JS), dividiendo el código en dos archivos para mantener el orden:

  * **`ReservaService.js`:** Encapsula las llamadas asíncronas (`fetch`) o la comunicación con los datos, manteniendo la misma filosofía de "Servicios" que en el backend.
  * **`reservas.js`:** Se encarga de manipular el DOM, capturar los eventos del usuario en la interfaz y actualizar la vista utilizando los datos proporcionados por el servicio.
* **Punto de entrada y Servicios PHP (`public/index.php` y `src/Services/ReservaService.php`):** Actúan como puente en el lado del servidor para procesar las peticiones de las vistas, obtener la información necesaria y renderizar las plantillas Twig correspondientes.

### 3. Estructura de Directorios del Frontend

```
.
├── composer.json
├── composer.lock
├── public
│   ├── index.php
│   └── js
│       ├── ReservaService.js
│       └── reservas.js
├── README.md
├── src
│   └── Services
│       └── ReservaService.php
└── templates
    ├── error
    │   └── 404.html.twig
    ├── layout
    │   └── base.html.twig
    └── reservas
        ├── detalle.html.twig
        └── index.html.twig
```

## **Qué harías diferente con más tiempo.**

Si dispusiera de más tiempo para iterar sobre el proyecto, las prioridades de evolución serían las siguientes:

1. **Autenticación y Seguridad mediante Tokens:**

   * Lo primero y más importante sería implementar un **sistema de usuarios** para interactuar con la aplicación.
   * Añadiría autenticación basada en **Tokens (como JWT)** para proteger los *endpoints* de la API, aportando una capa adicional de seguridad en cada petición.
2. **Control de Acceso Basado en Roles (RBAC) y Entidad de Edificios:**

   * Implementaría distintos **roles de usuario** (por ejemplo, *Administrador* y *Usuario estándar*) para separar los permisos y las pantallas a las que tiene acceso cada perfil.
   * Crearía una entidad específica para gestionar los **Edificios**, evitando que se puedan introducir nombres o edificios arbitrarios al hacer una reserva:
     * **Rol Administrador:** Tendría acceso a un panel de gestión donde podría dar de alta nuevos edificios y visualizar el listado global de todas las reservas existentes.
     * **Rol Usuario:** Únicamente tendría acceso a la vista de edificios disponibles para ser reservados y a la creación/consulta de sus propias reservas.
3. **Profundización en el Frontend con Twig:**

   * Dedicaría tiempo a experimentar más a fondo con las capacidades avanzadas de **Twig** (uso de componentes reutilizables, *macros*, filtros personalizados, etc.) y a pulir el diseño visual para ofrecer pantallas más ricas e intuitivas.

---

## Escalabilidad: ¿Qué cambiaría para soportar 1000 reservas/hora?

Aunque hasta ahora no me he enfrentado a ese volumen de tráfico en mis aplicaciones personales, para garantizar que el servicio gestione **1000 reservas por hora** de forma fluida y sin cuellos de botella, aplicaría mejoras enfocadas en la **concurrencia y el procesamiento paralelo**:

* **Procesamiento multihilo / Concurrencia en la API:** Mejoraría la arquitectura del servidor y de la API para que pueda gestionar las peticiones de forma concurrente (mediante el uso de **hilos / *workers*** o herramientas de ejecución asíncrona en PHP como *Swoole* o *RoadRunner*, además de optimizar los procesos de *PHP-FPM*). Esto permitiría atender múltiples solicitudes de reserva de manera simultánea y mucho más fluida, evitando que una petición bloquee a las demás.
* **Control de concurrencia en Base de Datos:** Al procesar peticiones en paralelo con múltiples hilos, me aseguraría de reforzar el uso de transacciones y bloqueos en la base de datos para evitar condiciones de carrera (*race conditions*), garantizando que dos usuarios no puedan reservar exactamente el mismo espacio al mismo milisegundo.

---

## Autor

---

**Alberto Romero Pino**

* **Email** : [albertoromeropino2004@gmail.com](mailto:albertoromeropino2004@gmail.com)
* **LinkedIn** : [linkedin.com/in/alberto-romero-pino-8aa0a32ba](https://linkedin.com/in/alberto-romero-pino-8aa0a32ba)
* **GitHub:** [@AlbertoRomeroPino](https://github.com/AlbertoRomeroPino)
