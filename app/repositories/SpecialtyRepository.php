<?php
declare(strict_types=1);

/**
 * Repositorio de Especialidades Médicas
 * Diseñado para desacoplar la fuente de datos (actualmente archivos PHP; preparado para MySQL/PDO en Etapa 2)
 */

if (!defined('HSM_APP')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

class SpecialtyRepository
{
    /** @var array<int, array<string, mixed>>|null */
    private static ?array $cache = null;

    /** @var array<int, array<string, mixed>>|null */
    private static ?array $bookingCache = null;

    /**
     * Obtiene las 17 especialidades médicas
     * @return array<int, array<string, mixed>>
     */
    private const SELECT = "SELECT s.slug, s.name, s.description, s.badge, s.badge_type, s.icon, s.highlight_text,
                   GROUP_CONCAT(c.slug ORDER BY m.sort_order SEPARATOR ' ') AS categories
              FROM specialties s
         LEFT JOIN specialty_category_map m ON m.specialty_id = s.id
         LEFT JOIN specialty_categories c   ON c.id = m.category_id";

    public static function getAll(): array
    {
        if (self::$cache === null) {
            $rows = Database::fetchAll(self::SELECT . ' GROUP BY s.id ORDER BY s.sort_order ASC, s.id ASC');
            self::$cache = array_map(static fn(array $r): array => self::mapRow($r), $rows);
        }
        return self::$cache;
    }

    /**
     * Busca una especialidad por su identificador (slug)
     * @return array<string, mixed>|null
     */
    public static function findById(string $id): ?array
    {
        $rows = Database::fetchAll(
            self::SELECT . ' WHERE s.slug = :slug GROUP BY s.id LIMIT 1',
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
            'name'        => (string) $r['name'],
            'description' => (string) $r['description'],
            'category'    => (string) ($r['categories'] ?? ''),
            'badge'       => (string) $r['badge'],
        ];
        // Campos opcionales: solo existen en algunas especialidades
        if ($r['badge_type'] !== null) {
            $row['badgeType'] = (string) $r['badge_type'];
        }
        $row['icon'] = (string) $r['icon'];
        if ($r['highlight_text'] !== null) {
            $row['highlightText'] = (string) $r['highlight_text'];
        }
        return $row;
    }

    /**
     * Obtiene las categorías de filtro de la página Especialidades
     * @return array<int, array{id: string, label: string}>
     */
    public static function getFilterCategories(): array
    {
        return [
            ['id' => 'all',       'label' => 'Todas las especialidades'],
            ['id' => 'mujer',     'label' => 'Salud de la mujer y maternidad'],
            ['id' => 'cirugia',   'label' => 'Cirugía y procedimientos'],
            ['id' => 'clinica',   'label' => 'Medicina clínica y adultos'],
            ['id' => 'mental',    'label' => 'Salud mental y neurociencias'],
            ['id' => 'pediatria', 'label' => 'Especialidades quirúrgicas y pediátricas'],
        ];
    }

    /**
     * Lista de especialidades con su ID primario de BD para validación y registro de citas
     * @return array<int, array{db_id: int, id: string, name: string}>
     */
    public static function getAllForBooking(): array
    {
        if (self::$bookingCache === null) {
            $rows = Database::fetchAll(
                'SELECT id, slug, name FROM specialties ORDER BY sort_order ASC, id ASC'
            );
            self::$bookingCache = array_map(
                static fn(array $r): array => [
                    'db_id' => (int) $r['id'],
                    'id'    => (string) $r['slug'],
                    'name'  => (string) $r['name'],
                ],
                $rows
            );
        }

        return self::$bookingCache;
    }

    /**
     * Busca una especialidad por slug o nombre exacto para agendamiento
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
            "SELECT s.*,
                    GROUP_CONCAT(DISTINCT c.label ORDER BY m.sort_order SEPARATOR ', ') AS categories_label,
                    (SELECT COUNT(*) FROM doctor_specialties ds WHERE ds.specialty_id = s.id) AS doctors_count
               FROM specialties s
          LEFT JOIN specialty_category_map m ON m.specialty_id = s.id
          LEFT JOIN specialty_categories c   ON c.id = m.category_id
           GROUP BY s.id
           ORDER BY s.sort_order ASC, s.id ASC"
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function adminGetCategories(): array
    {
        return Database::fetchAll('SELECT id, slug, label FROM specialty_categories ORDER BY id ASC');
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function adminFindById(int $id): ?array
    {
        $rows = Database::fetchAll('SELECT * FROM specialties WHERE id = :id LIMIT 1', ['id' => $id]);
        if ($rows === []) {
            return null;
        }
        $spec = $rows[0];
        $catRows = Database::fetchAll(
            'SELECT category_id FROM specialty_category_map WHERE specialty_id = :id ORDER BY sort_order ASC',
            ['id' => $id]
        );
        $spec['category_ids'] = array_map(static fn(array $c): int => (int) $c['category_id'], $catRows);
        return $spec;
    }

    public static function slugExists(string $slug, ?int $excludeId = null): bool
    {
        if ($excludeId !== null) {
            $rows = Database::fetchAll(
                'SELECT id FROM specialties WHERE slug = :slug AND id <> :id LIMIT 1',
                ['slug' => $slug, 'id' => $excludeId]
            );
        } else {
            $rows = Database::fetchAll(
                'SELECT id FROM specialties WHERE slug = :slug LIMIT 1',
                ['slug' => $slug]
            );
        }
        return $rows !== [];
    }

    /**
     * @param array<string, mixed> $data
     * @param array<int, int>      $categoryIds
     */
    public static function adminSave(?int $id, array $data, array $categoryIds): int
    {
        $pdo = Database::getConnection();

        try {
            $pdo->beginTransaction();

            $params = [
                'slug'           => $data['slug'],
                'name'           => $data['name'],
                'description'    => $data['description'],
                'badge'          => $data['badge'],
                'badge_type'     => $data['badge_type'],
                'icon'           => $data['icon'],
                'highlight_text' => $data['highlight_text'],
                'sort_order'     => $data['sort_order'],
            ];

            if ($id !== null && $id > 0) {
                $params['id'] = $id;
                $stmt = $pdo->prepare(
                    'UPDATE specialties
                        SET slug = :slug, name = :name, description = :description,
                            badge = :badge, badge_type = :badge_type, icon = :icon,
                            highlight_text = :highlight_text, sort_order = :sort_order
                      WHERE id = :id'
                );
                $stmt->execute($params);
                $specId = $id;
            } else {
                $stmt = $pdo->prepare(
                    'INSERT INTO specialties (slug, name, description, badge, badge_type, icon, highlight_text, sort_order)
                     VALUES (:slug, :name, :description, :badge, :badge_type, :icon, :highlight_text, :sort_order)'
                );
                $stmt->execute($params);
                $specId = (int) $pdo->lastInsertId();
            }

            $pdo->prepare('DELETE FROM specialty_category_map WHERE specialty_id = :id')->execute(['id' => $specId]);

            $insCat = $pdo->prepare(
                'INSERT INTO specialty_category_map (specialty_id, category_id, sort_order)
                 VALUES (:spec_id, :cat_id, :ord)'
            );
            $ord = 0;
            foreach (array_unique($categoryIds) as $catId) {
                $catId = (int) $catId;
                if ($catId > 0) {
                    $insCat->execute([
                        'spec_id' => $specId,
                        'cat_id'  => $catId,
                        'ord'     => $ord++,
                    ]);
                }
            }

            $pdo->commit();
            self::$cache = null;
            self::$bookingCache = null;

            return $specId;
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('[HSM][SPECIALTY_ADMIN] ' . $e->getMessage());
            throw new DataSourceException('No se pudo guardar la especialidad médica.');
        }
    }

    public static function adminDelete(int $id): void
    {
        Database::execute('DELETE FROM specialties WHERE id = :id', ['id' => $id]);
        self::$cache = null;
        self::$bookingCache = null;
    }
}
