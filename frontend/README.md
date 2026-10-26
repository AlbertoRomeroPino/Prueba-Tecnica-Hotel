```
frontend/
├── public/                 <-- La única carpeta accesible desde el navegador
│   ├── css/                # Archivos de estilos
│   ├── js/                 # Scripts del frontend (validaciones, etc.)
│   ├── img/                # Imágenes
│   └── index.php           # "Front Controller" (Punto de entrada único)
├── src/                    <-- Toda tu lógica de PHP
│   ├── Controllers/        # Reciben la URL, piden datos al Service y renderizan Twig
│   └── Services/           # Se conectan a tu API backend (GET/POST a /reservas)
├── templates/              <-- Tus vistas
│   ├── layout/             # Plantilla base (base.html.twig, header, footer)
│   └── reservas/           # Plantillas específicas (index.html.twig, detalle.html.twig)
├── config/                 <-- Configuración global
│   └── config.php          # Variables como la URL base de tu API backend
├── vendor/                 # Código de Composer (Twig, Guzzle, etc.)
├── composer.json
└── .gitignore
```
