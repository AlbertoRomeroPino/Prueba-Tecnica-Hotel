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


## **Qué harías diferente con más tiempo.**


## Escalabilidad** : si este servicio recibiera 1000 reservas/hora, ¿qué cambiarías?
