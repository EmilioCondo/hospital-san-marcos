<?php
declare(strict_types=1);

/**
 * Repositorio de Servicios Clínicos y Fichas Operativas
 * Diseñado para desacoplar la fuente de datos (actualmente archivos PHP; preparado para MySQL/PDO en Etapa 2)
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
     * Carga la estructura completa de servicios y detalles
     * @return array<string, mixed>
     */
    private static function loadData(): array
    {
        if (self::$cache === null) {
            // Servicios + características
            $features = [];
            foreach (Database::fetchAll(
                'SELECT service_id, feature FROM service_features ORDER BY service_id ASC, sort_order ASC, id ASC'
            ) as $f) {
                $features[(int) $f['service_id']][] = (string) $f['feature'];
            }

            $services = [];
            foreach (Database::fetchAll(
                'SELECT id, slug, name, tag, tag_class, description, icon, detail_key
                   FROM services ORDER BY sort_order ASC, id ASC'
            ) as $s) {
                $services[] = [
                    'id'          => (string) $s['slug'],
                    'name'        => (string) $s['name'],
                    'tag'         => (string) $s['tag'],
                    'tagClass'    => (string) $s['tag_class'],
                    'description' => (string) $s['description'],
                    'icon'        => (string) $s['icon'],
                    'features'    => $features[(int) $s['id']] ?? [],
                    'detailKey'   => (string) $s['detail_key'],
                ];
            }

            // Fichas operativas + ítems (alcance / preparación)
            $items = [];
            foreach (Database::fetchAll(
                'SELECT detail_id, type, item FROM service_detail_items ORDER BY detail_id ASC, type ASC, sort_order ASC, id ASC'
            ) as $i) {
                $items[(int) $i['detail_id']][(string) $i['type']][] = (string) $i['item'];
            }

            $details = [];
            foreach (Database::fetchAll(
                'SELECT id, detail_key, title, tag, description, icon, time_notice, prep_notice
                   FROM service_details ORDER BY sort_order ASC, id ASC'
            ) as $d) {
                $details[(string) $d['detail_key']] = [
                    'title'      => (string) $d['title'],
                    'tag'        => (string) $d['tag'],
                    'desc'       => (string) $d['description'],
                    'icon'       => (string) $d['icon'],
                    'scope'      => $items[(int) $d['id']]['scope'] ?? [],
                    'prep'       => $items[(int) $d['id']]['prep'] ?? [],
                    'timeNotice' => (string) $d['time_notice'],
                    'prepNotice' => (string) $d['prep_notice'],
                ];
            }

            self::$cache = ['services' => $services, 'detailsMap' => $details];
        }
        return self::$cache;
    }

    /**
     * Obtiene los 6 servicios principales del catálogo
     * @return array<int, array<string, mixed>>
     */
    public static function getAll(): array
    {
        $data = self::loadData();
        return $data['services'] ?? [];
    }

    /**
     * Obtiene el mapa de las 4 fichas operativas interactivas
     * @return array<string, array<string, mixed>>
     */
    public static function getDetailsMap(): array
    {
        $data = self::loadData();
        return $data['detailsMap'] ?? [];
    }

    /**
     * Obtiene una ficha operativa por su clave (por defecto 'laboratorio')
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
            'SELECT service_id, feature FROM service_features ORDER BY service_id ASC, sort_order ASC, id ASC'
        ) as $f) {
            $featMap[(int) $f['service_id']][] = (string) $f['feature'];
        }

        $rows = Database::fetchAll('SELECT * FROM services ORDER BY sort_order ASC, id ASC');
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
        $rows = Database::fetchAll('SELECT * FROM services WHERE id = :id LIMIT 1', ['id' => $id]);
        if ($rows === []) {
            return null;
        }
        $svc = $rows[0];
        $fRows = Database::fetchAll(
            'SELECT feature FROM service_features WHERE service_id = :id ORDER BY sort_order ASC, id ASC',
            ['id' => $id]
        );
        $svc['features'] = array_map(static fn(array $f): string => (string) $f['feature'], $fRows);
        return $svc;
    }

    public static function serviceSlugExists(string $slug, ?int $excludeId = null): bool
    {
        if ($excludeId !== null) {
            $rows = Database::fetchAll(
                'SELECT id FROM services WHERE slug = :slug AND id <> :id LIMIT 1',
                ['slug' => $slug, 'id' => $excludeId]
            );
        } else {
            $rows = Database::fetchAll(
                'SELECT id FROM services WHERE slug = :slug LIMIT 1',
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
                'slug'        => $data['slug'],
                'name'        => $data['name'],
                'tag'         => $data['tag'],
                'tag_class'   => $data['tag_class'],
                'description' => $data['description'],
                'icon'        => $data['icon'],
                'detail_key'  => $data['detail_key'],
                'sort_order'  => $data['sort_order'],
            ];

            if ($id !== null && $id > 0) {
                $params['id'] = $id;
                $pdo->prepare(
                    'UPDATE services
                        SET slug = :slug, name = :name, tag = :tag, tag_class = :tag_class,
                            description = :description, icon = :icon, detail_key = :detail_key, sort_order = :sort_order
                      WHERE id = :id'
                )->execute($params);
                $svcId = $id;
            } else {
                $pdo->prepare(
                    'INSERT INTO services (slug, name, tag, tag_class, description, icon, detail_key, sort_order)
                     VALUES (:slug, :name, :tag, :tag_class, :description, :icon, :detail_key, :sort_order)'
                )->execute($params);
                $svcId = (int) $pdo->lastInsertId();
            }

            $pdo->prepare('DELETE FROM service_features WHERE service_id = :id')->execute(['id' => $svcId]);
            $insFeat = $pdo->prepare(
                'INSERT INTO service_features (service_id, feature, sort_order) VALUES (:svc_id, :feat, :ord)'
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
        Database::execute('DELETE FROM services WHERE id = :id', ['id' => $id]);
        self::$cache = null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function adminGetDetails(): array
    {
        $itemsMap = [];
        foreach (Database::fetchAll(
            'SELECT detail_id, type, item FROM service_detail_items ORDER BY detail_id ASC, type ASC, sort_order ASC, id ASC'
        ) as $it) {
            $itemsMap[(int) $it['detail_id']][(string) $it['type']][] = (string) $it['item'];
        }

        $rows = Database::fetchAll('SELECT * FROM service_details ORDER BY sort_order ASC, id ASC');
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
        $rows = Database::fetchAll('SELECT * FROM service_details WHERE id = :id LIMIT 1', ['id' => $id]);
        if ($rows === []) {
            return null;
        }
        $det = $rows[0];
        $itRows = Database::fetchAll(
            'SELECT type, item FROM service_detail_items WHERE detail_id = :id ORDER BY type ASC, sort_order ASC, id ASC',
            ['id' => $id]
        );
        $det['scope'] = [];
        $det['prep']  = [];
        foreach ($itRows as $it) {
            $det[(string) $it['type']][] = (string) $it['item'];
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
                'UPDATE service_details
                    SET title = :title, tag = :tag, description = :description,
                        icon = :icon, time_notice = :time_notice, prep_notice = :prep_notice
                  WHERE id = :id'
            )->execute([
                'title'       => $data['title'],
                'tag'         => $data['tag'],
                'description' => $data['description'],
                'icon'        => $data['icon'],
                'time_notice' => $data['time_notice'],
                'prep_notice' => $data['prep_notice'],
                'id'          => $id,
            ]);

            $pdo->prepare('DELETE FROM service_detail_items WHERE detail_id = :id')->execute(['id' => $id]);

            $insItem = $pdo->prepare(
                'INSERT INTO service_detail_items (detail_id, type, item, sort_order)
                 VALUES (:det_id, :type, :item, :ord)'
            );

            $ord = 0;
            foreach ($scopeItems as $item) {
                $insItem->execute(['det_id' => $id, 'type' => 'scope', 'item' => $item, 'ord' => $ord++]);
            }
            $ord = 0;
            foreach ($prepItems as $item) {
                $insItem->execute(['det_id' => $id, 'type' => 'prep', 'item' => $item, 'ord' => $ord++]);
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
