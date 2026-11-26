<?php
declare(strict_types=1);

/**
 * Conexión PDO a MySQL/MariaDB utilizando consultas preparadas reales
 */

if (!defined('HSM_APP')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

/**
 * Error de origen de datos: el mensaje es genérico y seguro de mostrar;
 * el detalle técnico solo se registra en el log del servidor.
 */
class DataSourceException extends RuntimeException
{
}

class Database
{
    private static ?PDO $instance = null;

    private function __construct()
    {
    }

    /**
     * Ejecuta una consulta preparada y devuelve todas las filas.
     * Ante cualquier fallo registra el detalle en el log y lanza una excepción genérica.
     *
     * @param array<string, mixed>|array<int, mixed> $params
     * @return array<int, array<string, mixed>>
     */
    public static function fetchAll(string $sql, array $params = []): array
    {
        try {
            $stmt = self::getConnection()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('[HSM][DB] ' . $e->getMessage());
            throw new DataSourceException('Servicio de datos no disponible.', 0, null);
        }
    }

    /**
     * Ejecuta una sentencia preparada de escritura (INSERT / UPDATE / DELETE).
     *
     * @param array<string, mixed>|array<int, mixed> $params
     */
    public static function execute(string $sql, array $params = []): int
    {
        try {
            $stmt = self::getConnection()->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log('[HSM][DB] ' . $e->getMessage());
            throw new DataSourceException('Servicio de datos no disponible.', 0, null);
        }
    }

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                DB_HOST,
                DB_PORT,
                DB_NAME,
                DB_CHARSET
            );

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
        }

        return self::$instance;
    }
}
