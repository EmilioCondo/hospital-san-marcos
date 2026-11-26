<?php
declare(strict_types=1);

/**
 * Repositorio PDO de Servicios Clínicos y Fichas Operativas
 * Tablas: `servicios`, `servicio_caracteristicas`, `servicio_fichas`, `servicio_ficha_items`
 * (Sin relación con `preguntas_frecuentes` ni con `citas`).
 */

if (!defined('HSM_APP')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

class ServiceRepository
{
    /** @var array<string, mixed>|null */
    private static ?array $cache = null;

    /**
     * @return array<string, mixed>
     */
    private static function loadData(): array
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
                'SELECT id, slug, nombre, etiqueta, clase_etiqueta, descripcion, icono, clave_ficha
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
                    'detailKey'   => (string) $s['clave_ficha'],
                ];
            }

            $items = [];
            foreach (Database::fetchAll(
                'SELECT ficha_id, tipo, descripcion FROM servicio_ficha_items ORDER BY ficha_id ASC, tipo ASC, orden ASC, id ASC'
            ) as $i) {
                $key = ((string) $i['tipo']) === 'alcance' ? 'scope' : 'prep';
                $items[(int) $i['ficha_id']][$key][] = (string) $i['descripcion'];
            }

            $details = [];
            foreach (Database::fetchAll(
                'SELECT id, clave_ficha, titulo, etiqueta, descripcion, icono, aviso_tiempo, aviso_preparacion
                   FROM servicio_fichas ORDER BY orden ASC, id ASC'
            ) as $d) {
                $details[(string) $d['clave_ficha']] = [
                    'title'      => (string) $d['titulo'],
                    'tag'        => (string) $d['etiqueta'],
                    'desc'       => (string) $d['descripcion'],
                    'icon'       => (string) $d['icono'],
                    'scope'      => $items[(int) $d['id']]['scope'] ?? [],
                    'prep'       => $items[(int) $d['id']]['prep'] ?? [],
                    'timeNotice' => (string) $d['aviso_tiempo'],
                    'prepNotice' => (string) $d['aviso_preparacion'],
                ];
            }

            self::$cache = ['services' => $services, 'detailsMap' => $details];
        }
        return self::$cache;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function getAll(): array
    {
        $data = self::loadData();
        return $data['services'] ?? [];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function getDetailsMap(): array
    {
        $data = self::loadData();
        return $data['detailsMap'] ?? [];
    }

    /**
     * @return array<string, mixed>
     */
    public static function getDetailByKey(string $key = 'laboratorio'): array
    {
        $map = self::getDetailsMap();
        return $map[$key] ?? $map['laboratorio'];
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
                    descripcion AS description, icono AS icon, clave_ficha AS detail_key, orden AS sort_order
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
                    descripcion AS description, icono AS icon, clave_ficha AS detail_key, orden AS sort_order
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
                'clave_ficha'    => $data['detail_key'],
                'orden'          => $data['sort_order'],
            ];

            if ($id !== null && $id > 0) {
                $params['id'] = $id;
                $pdo->prepare(
                    'UPDATE servicios
                        SET slug = :slug, nombre = :nombre, etiqueta = :etiqueta, clase_etiqueta = :clase_etiqueta,
                            descripcion = :descripcion, icono = :icono, clave_ficha = :clave_ficha, orden = :orden
                      WHERE id = :id'
                )->execute($params);
                $svcId = $id;
            } else {
                $pdo->prepare(
                    'INSERT INTO servicios (slug, nombre, etiqueta, clase_etiqueta, descripcion, icono, clave_ficha, orden)
                     VALUES (:slug, :nombre, :etiqueta, :clase_etiqueta, :descripcion, :icono, :clave_ficha, :orden)'
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

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function adminGetDetails(): array
    {
        $itemsMap = [];
        foreach (Database::fetchAll(
            'SELECT ficha_id, tipo, descripcion FROM servicio_ficha_items ORDER BY ficha_id ASC, tipo ASC, orden ASC, id ASC'
        ) as $it) {
            $k = ((string) $it['tipo']) === 'alcance' ? 'scope' : 'prep';
            $itemsMap[(int) $it['ficha_id']][$k][] = (string) $it['descripcion'];
        }

        $rows = Database::fetchAll(
            'SELECT id, clave_ficha AS detail_key, titulo AS title, etiqueta AS tag,
                    descripcion AS description, icono AS icon,
                    aviso_tiempo AS time_notice, aviso_preparacion AS prep_notice, orden AS sort_order
               FROM servicio_fichas ORDER BY orden ASC, id ASC'
        );
        foreach ($rows as &$r) {
            $did = (int) $r['id'];
            $r['scope'] = $itemsMap[$did]['scope'] ?? [];
            $r['prep']  = $itemsMap[$did]['prep'] ?? [];
        }
        unset($r);

        return $rows;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function adminFindDetailById(int $id): ?array
    {
        $rows = Database::fetchAll(
            'SELECT id, clave_ficha AS detail_key, titulo AS title, etiqueta AS tag,
                    descripcion AS description, icono AS icon,
                    aviso_tiempo AS time_notice, aviso_preparacion AS prep_notice, orden AS sort_order
               FROM servicio_fichas WHERE id = :id LIMIT 1',
            ['id' => $id]
        );
        if ($rows === []) {
            return null;
        }
        $det = $rows[0];
        $itRows = Database::fetchAll(
            'SELECT tipo, descripcion FROM servicio_ficha_items WHERE ficha_id = :id ORDER BY tipo ASC, orden ASC, id ASC',
            ['id' => $id]
        );
        $det['scope'] = [];
        $det['prep']  = [];
        foreach ($itRows as $it) {
            $k = ((string) $it['tipo']) === 'alcance' ? 'scope' : 'prep';
            $det[$k][] = (string) $it['descripcion'];
        }
        return $det;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<int, string>   $scopeItems
     * @param array<int, string>   $prepItems
     */
    public static function adminUpdateDetail(int $id, array $data, array $scopeItems, array $prepItems): void
    {
        $pdo = Database::getConnection();

        try {
            $pdo->beginTransaction();

            $pdo->prepare(
                'UPDATE servicio_fichas
                    SET titulo = :titulo, etiqueta = :etiqueta, descripcion = :descripcion,
                        icono = :icono, aviso_tiempo = :aviso_tiempo, aviso_preparacion = :aviso_preparacion
                  WHERE id = :id'
            )->execute([
                'titulo'            => $data['title'],
                'etiqueta'          => $data['tag'],
                'descripcion'       => $data['description'],
                'icono'             => $data['icon'],
                'aviso_tiempo'      => $data['time_notice'],
                'aviso_preparacion' => $data['prep_notice'],
                'id'                => $id,
            ]);

            $pdo->prepare('DELETE FROM servicio_ficha_items WHERE ficha_id = :id')->execute(['id' => $id]);

            $insItem = $pdo->prepare(
                'INSERT INTO servicio_ficha_items (ficha_id, tipo, descripcion, orden)
                 VALUES (:ficha_id, :tipo, :descripcion, :orden)'
            );

            $ord = 0;
            foreach ($scopeItems as $item) {
                $insItem->execute(['ficha_id' => $id, 'tipo' => 'alcance', 'descripcion' => $item, 'orden' => $ord++]);
            }
            $ord = 0;
            foreach ($prepItems as $item) {
                $insItem->execute(['ficha_id' => $id, 'tipo' => 'preparacion', 'descripcion' => $item, 'orden' => $ord++]);
            }

            $pdo->commit();
            self::$cache = null;
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('[HSM][SERVICE_DETAIL_ADMIN] ' . $e->getMessage());
            throw new DataSourceException('No se pudo actualizar la ficha operativa.');
        }
    }
}
