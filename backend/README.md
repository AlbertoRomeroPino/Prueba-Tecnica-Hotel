```
/backend
│
├── /public                 # Document Root. Solo lo accesible por el navegador.
│   ├── index.php           # Front Controller (Todas las peticiones entran por aquí).
│   └── .htaccess           # Redirige todo el tráfico a index.php (si usas Apache).
│
├── /src                    # Código fuente de la aplicación (Namespace App\)
│   ├── /Config             # Archivos de configuración (Ej. variables de BD).
│   ├── /Controllers        # Controladores: Reciben la Request, llaman al Service, devuelven JSON.
│   ├── /Database           # Conexión a la BD (Ej. una clase Database usando PDO).
│   ├── /Entities           # Modelos de datos puros (Reserva, ReservaEvento). Solo propiedades y getters/setters.
│   ├── /Exceptions         # (Bonus) Clases de Excepciones personalizadas para el manejo centralizado.
│   ├── /Repositories       # Capa de Acceso a Datos: Aquí van las consultas SQL puras.
│   ├── /Router             # Enrutador nativo sencillo para mapear URLs a Controladores.
│   ├── /Services           # Lógica de negocio: Validaciones complejas, crear reserva + evento.
│   └── /Validators         # Clases de validación de inputs (fechas, emails, campos obligatorios).
│   
├── /database               # Obligatorio por los requisitos.
│   └── init.sql            # Script DDL (CREATE TABLES) y DML (INSERTs con datos de prueba).
│
├── /tests                  # Pruebas automatizadas.
│   └── /Unit               # Tests unitarios para Servicios y Validadores (PHPUnit).
│
├── composer.json           # Define el autoload PSR-4 y dependencias de dev (PHPUnit).
├── phpunit.xml             # Configuración para ejecutar los tests.
└── README.md               # Instrucciones MUY claras de cómo levantar el proyecto y la BD.
```
