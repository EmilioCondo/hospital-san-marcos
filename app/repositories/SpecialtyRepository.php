<?php
declare(strict_types=1);

/**
 * Repositorio PDO de Especialidades Médicas (tabla `especialidades`)
 */

if (!defined('HSM_APP')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

class SpecialtyRepository
{
    /** @var array<int, array<string, mixed>>|null */
    private static ?array $cache = null;

    /** @var array<int, array{db_id: int, id: string, name: string}>|null */
    private static ?array $bookingCache = null;

    /**
     * Obtiene las especialidades médicas del catálogo
     * @return array<int, array<string, mixed>>
     */
    public static function getAll(): array
    {
        if (self::$cache === null) {
            $rows = Database::fetchAll(
                'SELECT slug, nombre, descripcion, insignia, tipo_insignia, icono, texto_destacado
                   FROM especialidades
               ORDER BY orden ASC, id ASC'
            );
            self::$cache = array_map(static fn(array $r): array => self::mapRow($r), $rows);
        }
        return self::$cache;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function findById(string $id): ?array
    {
        $rows = Database::fetchAll(
            'SELECT slug, nombre, descripcion, insignia, tipo_insignia, icono, texto_destacado
               FROM especialidades
              WHERE slug = :slug
              LIMIT 1',
            ['slug' => $id]
        );
        return $rows === [] ? null : self::mapRow($rows[0]);
    }

    /**
     * @param array<string, mixed> $r
     * @return array<string, mixed>
     */
    private static function mapRow(array $r): array
    {
        $row = [
            'id'          => (string) $r['slug'],
            'name'        => (string) $r['nombre'],
            'description' => (string) $r['descripcion'],
            'badge'       => (string) $r['insignia'],
        ];
        if ($r['tipo_insignia'] !== null) {
            $row['badgeType'] = (string) $r['tipo_insignia'];
        }
        $row['icon'] = (string) $r['icono'];
        if ($r['texto_destacado'] !== null) {
            $row['highlightText'] = (string) $r['texto_destacado'];
        }
        return $row;
    }

    /**
     * @return array<int, array{db_id: int, id: string, name: string}>
     */
    public static function getAllForBooking(): array
    {
        if (self::$bookingCache === null) {
            $rows = Database::fetchAll(
                'SELECT id, slug, nombre FROM especialidades ORDER BY orden ASC, id ASC'
            );
            self::$bookingCache = array_map(
                static fn(array $r): array => [
                    'db_id' => (int) $r['id'],
                    'id'    => (string) $r['slug'],
                    'name'  => (string) $r['nombre'],
                ],
                $rows
            );
        }
        return self::$bookingCache;
    }

    /**
     * @return array{db_id: int, id: string, name: string}|null
     */
    public static function findForBooking(string $slugOrName): ?array
    {
        $needle = trim($slugOrName);
        if ($needle === '') {
            return null;
        }

        foreach (self::getAllForBooking() as $spec) {
            if (strcasecmp($spec['id'], $needle) === 0 || strcasecmp($spec['name'], $needle) === 0) {
                return $spec;
            }
        }

        return null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function adminGetAll(): array
    {
        return Database::fetchAll(
            'SELECT e.id, e.slug, e.nombre AS name, e.descripcion AS description,
                    e.insignia AS badge, e.tipo_insignia AS badge_type,
                    e.icono AS icon, e.texto_destacado AS highlight_text, e.orden AS sort_order,
                    (SELECT COUNT(*) FROM medico_especialidades me WHERE me.especialidad_id = e.id) AS doctors_count
               FROM especialidades e
           ORDER BY e.orden ASC, e.id ASC'
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function adminFindById(int $id): ?array
    {
        $rows = Database::fetchAll(
            'SELECT id, slug, nombre AS name, descripcion AS description,
                    insignia AS badge, tipo_insignia AS badge_type,
                    icono AS icon, texto_destacado AS highlight_text, orden AS sort_order
               FROM especialidades
              WHERE id = :id
              LIMIT 1',
            ['id' => $id]
        );
        return $rows[0] ?? null;
    }

    public static function slugExists(string $slug, ?int $excludeId = null): bool
    {
        if ($excludeId !== null) {
            $rows = Database::fetchAll(
                'SELECT id FROM especialidades WHERE slug = :slug AND id <> :id LIMIT 1',
                ['slug' => $slug, 'id' => $excludeId]
            );
        } else {
            $rows = Database::fetchAll(
                'SELECT id FROM especialidades WHERE slug = :slug LIMIT 1',
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
            'slug'            => $data['slug'],
            'nombre'          => $data['name'],
            'descripcion'     => $data['description'],
            'insignia'        => $data['badge'],
            'tipo_insignia'   => $data['badge_type'],
            'icono'           => $data['icon'],
            'texto_destacado' => $data['highlight_text'],
            'orden'           => $data['sort_order'],
        ];

        if ($id !== null && $id > 0) {
            $params['id'] = $id;
            Database::execute(
                'UPDATE especialidades
                    SET slug = :slug, nombre = :nombre, descripcion = :descripcion,
                        insignia = :insignia, tipo_insignia = :tipo_insignia, icono = :icono,
                        texto_destacado = :texto_destacado, orden = :orden
                  WHERE id = :id',
                $params
            );
            self::$cache = null;
            self::$bookingCache = null;
            return $id;
        }

        Database::execute(
            'INSERT INTO especialidades (slug, nombre, descripcion, insignia, tipo_insignia, icono, texto_destacado, orden)
             VALUES (:slug, :nombre, :descripcion, :insignia, :tipo_insignia, :icono, :texto_destacado, :orden)',
            $params
        );
        self::$cache = null;
        self::$bookingCache = null;
        return (int) Database::getConnection()->lastInsertId();
    }

    public static function adminDelete(int $id): void
    {
        Database::execute('DELETE FROM especialidades WHERE id = :id', ['id' => $id]);
        self::$cache = null;
        self::$bookingCache = null;
    }
}
