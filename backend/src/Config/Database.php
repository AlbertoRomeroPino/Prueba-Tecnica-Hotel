<?php

class Database
{
    private static ?PDO $connection = null;

    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            $host = 'localhost';
            $db   = 'hotel_DB';
            $user = 'root';
            $password = '';
            $charset = 'utf8mb4';

            $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            try {
                self::$conexion = new PDO($dsn, $user, $pass, $opciones);
            } catch (\PDOException $e) {
                throw new \PDOException("Error de conexión: " . $e->getMessage(), (int)$e->getCode());
            }
        }
        self::$conexion->exec("SET time_zone = '+00:00'");
        return self::$conexion;
    }
}


?> 