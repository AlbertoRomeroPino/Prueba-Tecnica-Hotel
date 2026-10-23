<?php
declare(strict_types=1);

class Database
{
    private static ?PDO $connection = null;

    /**
     * Constructor privado para evitar instanciación externa (Patrón Singleton estricto)
     */
    private function __construct() {}

    /**
     * Método mágico clone privado para evitar duplicación de la instancia
     */
    private function __clone() {}

    /**
     * Devuelve la instancia de PDO para la conexión a la base de datos.
     * Si la conexión aún no se ha establecido, la crea.
     */
    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            // En un entorno profesional, esto vendría de getenv() o un archivo .env
            $host     = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: '127.0.0.1';
            $db       = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'Hotel_DB';
            $user     = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root';
            $password = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: '';
            $charset  = 'utf8mb4';

            $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$connection = new PDO($dsn, $user, $password, $options);
                // La configuración de la zona horaria debe ejecutarse SOLO al crear la conexión
                self::$connection->exec("SET time_zone = '+00:00'");
            } catch (PDOException $e) {
                // Enmascaramos el mensaje real del error para no filtrar credenciales al frontend
                throw new Exception("Error crítico al conectar con la base de datos.", 500);
            }
        }
        
        return self::$connection;
    }
}