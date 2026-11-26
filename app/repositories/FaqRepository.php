<?php
declare(strict_types=1);

/**
 * Repositorio PDO de Preguntas Frecuentes (tabla `preguntas_frecuentes`)
 * Independiente de `servicios`.
 */

if (!defined('HSM_APP')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

class FaqRepository
{
    /** @var array<int, array<string, string>>|null */
    private static ?array $cache = null;

    /**
     * @return array<int, array<string, string>>
     */
    public static function getAll(): array
    {
        if (self::$cache === null) {
            $rows = Database::fetchAll(
                'SELECT slug, pregunta, respuesta FROM preguntas_frecuentes WHERE activo = :activo ORDER BY orden ASC, id ASC',
                ['activo' => 1]
            );
            self::$cache = array_map(static fn(array $r): array => self::mapRow($r), $rows);
        }
        return self::$cache;
    }

    /**
     * @return array<string, string>|null
     */
    public static function findById(string $id): ?array
    {
        $rows = Database::fetchAll(
            'SELECT slug, pregunta, respuesta FROM preguntas_frecuentes WHERE slug = :slug AND activo = :activo LIMIT 1',
            ['slug' => $id, 'activo' => 1]
        );
        return $rows === [] ? null : self::mapRow($rows[0]);
    }

    /**
     * @param array<string, mixed> $r
     * @return array<string, string>
     */
    private static function mapRow(array $r): array
    {
        return [
            'id'       => (string) $r['slug'],
            'question' => (string) $r['pregunta'],
            'answer'   => (string) $r['respuesta'],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function adminGetAll(): array
    {
        return Database::fetchAll(
            'SELECT id, slug, pregunta AS question, respuesta AS answer, orden AS sort_order, activo AS is_active
               FROM preguntas_frecuentes ORDER BY orden ASC, id ASC'
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function adminFindById(int $id): ?array
    {
        $rows = Database::fetchAll(
            'SELECT id, slug, pregunta AS question, respuesta AS answer, orden AS sort_order, activo AS is_active
               FROM preguntas_frecuentes WHERE id = :id LIMIT 1',
            ['id' => $id]
        );
        return $rows[0] ?? null;
    }

    public static function slugExists(string $slug, ?int $excludeId = null): bool
    {
        if ($excludeId !== null) {
            $rows = Database::fetchAll(
                'SELECT id FROM preguntas_frecuentes WHERE slug = :slug AND id <> :id LIMIT 1',
                ['slug' => $slug, 'id' => $excludeId]
            );
        } else {
            $rows = Database::fetchAll(
                'SELECT id FROM preguntas_frecuentes WHERE slug = :slug LIMIT 1',
                ['slug' => $slug]
            );
        }
        return $rows !== [];
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function adminSave(?int $id, array $data): int
    {
        $params = [
            'slug'      => $data['slug'],
            'pregunta'  => $data['question'],
            'respuesta' => $data['answer'],
            'orden'     => $data['sort_order'],
            'activo'    => $data['is_active'] ? 1 : 0,
        ];

        if ($id !== null && $id > 0) {
            $params['id'] = $id;
            Database::execute(
                'UPDATE preguntas_frecuentes
                    SET slug = :slug, pregunta = :pregunta, respuesta = :respuesta,
                        orden = :orden, activo = :activo
                  WHERE id = :id',
                $params
            );
            self::$cache = null;
            return $id;
        }

        Database::execute(
            'INSERT INTO preguntas_frecuentes (slug, pregunta, respuesta, orden, activo)
             VALUES (:slug, :pregunta, :respuesta, :orden, :activo)',
            $params
        );
        self::$cache = null;
        return (int) Database::getConnection()->lastInsertId();
    }

    public static function adminToggleActive(int $id): void
    {
        Database::execute('UPDATE preguntas_frecuentes SET activo = IF(activo = 1, 0, 1) WHERE id = :id', ['id' => $id]);
        self::$cache = null;
    }

    public static function adminDelete(int $id): void
    {
        Database::execute('DELETE FROM preguntas_frecuentes WHERE id = :id', ['id' => $id]);
        self::$cache = null;
    }
}
