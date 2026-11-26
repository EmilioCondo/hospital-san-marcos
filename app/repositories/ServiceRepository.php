<?php
declare(strict_types=1);

/**
 * Repositorio PDO de Servicios Clínicos
 * Tablas: `servicios` y `servicio_caracteristicas`
 * (Independiente de `preguntas_frecuentes` y de `citas`).
 */

if (!defined('HSM_APP')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

class ServiceRepository
{
    /** @var array<int, array<string, mixed>>|null */
    private static ?array $cache = null;

    /**
     * Obtiene los servicios principales del catálogo con sus características
     * @return array<int, array<string, mixed>>
     */
    public static function getAll(): array
    {
        if (self::$cache === null) {
            $features = [];
            foreach (Database::fetchAll(
                'SELECT servicio_id, caracteristica FROM servicio_caracteristicas ORDER BY servicio_id ASC, orden ASC, id ASC'
            ) as $f) {
                $features[(int) $f['servicio_id']][] = (string) $f['caracteristica'];
            }

            $services = [];
            foreach (Database::fetchAll(
                'SELECT id, slug, nombre, etiqueta, clase_etiqueta, descripcion, icono
                   FROM servicios ORDER BY orden ASC, id ASC'
            ) as $s) {
                $services[] = [
                    'id'          => (string) $s['slug'],
                    'name'        => (string) $s['nombre'],
                    'tag'         => (string) $s['etiqueta'],
                    'tagClass'    => (string) $s['clase_etiqueta'],
                    'description' => (string) $s['descripcion'],
                    'icon'        => (string) $s['icono'],
                    'features'    => $features[(int) $s['id']] ?? [],
                ];
            }

            self::$cache = $services;
        }

        return self::$cache;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function adminGetServices(): array
    {
        $featMap = [];
        foreach (Database::fetchAll(
            'SELECT servicio_id, caracteristica FROM servicio_caracteristicas ORDER BY servicio_id ASC, orden ASC, id ASC'
        ) as $f) {
            $featMap[(int) $f['servicio_id']][] = (string) $f['caracteristica'];
        }

        $rows = Database::fetchAll(
            'SELECT id, slug, nombre AS name, etiqueta AS tag, clase_etiqueta AS tag_class,
                    descripcion AS description, icono AS icon, orden AS sort_order
               FROM servicios ORDER BY orden ASC, id ASC'
        );
        foreach ($rows as &$r) {
            $r['features'] = $featMap[(int) $r['id']] ?? [];
        }
        unset($r);

        return $rows;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function adminFindServiceById(int $id): ?array
    {
        $rows = Database::fetchAll(
            'SELECT id, slug, nombre AS name, etiqueta AS tag, clase_etiqueta AS tag_class,
                    descripcion AS description, icono AS icon, orden AS sort_order
               FROM servicios WHERE id = :id LIMIT 1',
            ['id' => $id]
        );
        if ($rows === []) {
            return null;
        }
        $svc = $rows[0];
        $fRows = Database::fetchAll(
            'SELECT caracteristica FROM servicio_caracteristicas WHERE servicio_id = :id ORDER BY orden ASC, id ASC',
            ['id' => $id]
        );
        $svc['features'] = array_map(static fn(array $f): string => (string) $f['caracteristica'], $fRows);
        return $svc;
    }

    public static function serviceSlugExists(string $slug, ?int $excludeId = null): bool
    {
        if ($excludeId !== null) {
            $rows = Database::fetchAll(
                'SELECT id FROM servicios WHERE slug = :slug AND id <> :id LIMIT 1',
                ['slug' => $slug, 'id' => $excludeId]
            );
        } else {
            $rows = Database::fetchAll(
                'SELECT id FROM servicios WHERE slug = :slug LIMIT 1',
                ['slug' => $slug]
            );
        }
        return $rows !== [];
    }

    /**
     * @param array<string, mixed> $data
     * @param array<int, string>   $features
     */
    public static function adminSaveService(?int $id, array $data, array $features): int
    {
        $pdo = Database::getConnection();

        try {
            $pdo->beginTransaction();

            $params = [
                'slug'           => $data['slug'],
                'nombre'         => $data['name'],
                'etiqueta'       => $data['tag'],
                'clase_etiqueta' => $data['tag_class'],
                'descripcion'    => $data['description'],
                'icono'          => $data['icon'],
                'orden'          => $data['sort_order'],
            ];

            if ($id !== null && $id > 0) {
                $params['id'] = $id;
                $pdo->prepare(
                    'UPDATE servicios
                        SET slug = :slug, nombre = :nombre, etiqueta = :etiqueta, clase_etiqueta = :clase_etiqueta,
                            descripcion = :descripcion, icono = :icono, orden = :orden
                      WHERE id = :id'
                )->execute($params);
                $svcId = $id;
            } else {
                $pdo->prepare(
                    'INSERT INTO servicios (slug, nombre, etiqueta, clase_etiqueta, descripcion, icono, orden)
                     VALUES (:slug, :nombre, :etiqueta, :clase_etiqueta, :descripcion, :icono, :orden)'
                )->execute($params);
                $svcId = (int) $pdo->lastInsertId();
            }

            $pdo->prepare('DELETE FROM servicio_caracteristicas WHERE servicio_id = :id')->execute(['id' => $svcId]);
            $insFeat = $pdo->prepare(
                'INSERT INTO servicio_caracteristicas (servicio_id, caracteristica, orden) VALUES (:svc_id, :feat, :ord)'
            );
            $ord = 0;
            foreach ($features as $feat) {
                $insFeat->execute([
                    'svc_id' => $svcId,
                    'feat'   => $feat,
                    'ord'    => $ord++,
                ]);
            }

            $pdo->commit();
            self::$cache = null;

            return $svcId;
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('[HSM][SERVICE_ADMIN] ' . $e->getMessage());
            throw new DataSourceException('No se pudo guardar el servicio clínico.');
        }
    }

    public static function adminDeleteService(int $id): void
    {
        Database::execute('DELETE FROM servicios WHERE id = :id', ['id' => $id]);
        self::$cache = null;
    }
}
