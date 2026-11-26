<?php
declare(strict_types=1);

/**
 * Repositorio de Médicos
 * Diseñado para desacoplar la fuente de datos (actualmente archivos PHP; preparado para MySQL/PDO en Etapa 2)
 */

if (!defined('HSM_APP')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

class DoctorRepository
{
    /** @var array<int, array<string, mixed>>|null */
    private static ?array $cache = null;

    /** @var array<int, array<string, mixed>>|null */
    private static ?array $bookingCache = null;

    /**
     * Obtiene la nómina completa de los 29 médicos
     * @return array<int, array<string, mixed>>
     */
    public static function getAll(): array
    {
        if (self::$cache === null) {
            $rows = Database::fetchAll(
                'SELECT slug, name, specialty_label, category, initials, location, accredited, description
                   FROM doctors
                  WHERE is_active = :active
               ORDER BY sort_order ASC, id ASC',
                ['active' => 1]
            );
            self::$cache = array_map(static fn(array $r): array => self::mapRow($r), $rows);
        }
        return self::$cache;
    }

    /**
     * Busca un médico por su identificador único (slug)
     * @return array<string, mixed>|null
     */
    public static function findById(string $id): ?array
    {
        $rows = Database::fetchAll(
            'SELECT slug, name, specialty_label, category, initials, location, accredited, description
               FROM doctors
              WHERE slug = :slug AND is_active = :active
              LIMIT 1',
            ['slug' => $id, 'active' => 1]
        );
        return $rows === [] ? null : self::mapRow($rows[0]);
    }

    /**
     * Convierte una fila de BD a la estructura que consumen las vistas
     * @param array<string, mixed> $r
     * @return array<string, mixed>
     */
    private static function mapRow(array $r): array
    {
        return [
            'id'          => (string) $r['slug'],
            'name'        => (string) $r['name'],
            'specialty'   => (string) $r['specialty_label'],
            'category'    => (string) $r['category'],
            'initials'    => (string) $r['initials'],
            'location'    => (string) $r['location'],
            'accredited'  => (bool) $r['accredited'],
            'description' => (string) $r['description'],
        ];
    }

    /**
     * Obtiene las categorías de filtro con el conteo dinámico de profesionales
     * @return array<int, array{id: string, label: string, count: int}>
     */
    public static function getCategoriesWithCounts(): array
    {
        $doctors = self::getAll();
        $baseCategories = [
            ['id' => 'all',              'label' => 'Todos'],
            ['id' => 'cirugia',          'label' => 'Cirugía y áreas quirúrgicas'],
            ['id' => 'ginecologia',      'label' => 'Ginecología'],
            ['id' => 'medicina-interna', 'label' => 'Medicina interna y clínica'],
            ['id' => 'traumatologia',    'label' => 'Traumatología'],
            ['id' => 'otras',            'label' => 'Otras especialidades'],
        ];

        $result = [];
        foreach ($baseCategories as $cat) {
            if ($cat['id'] === 'all') {
                $count = count($doctors);
            } else {
                $count = count(array_filter(
                    $doctors,
                    static fn(array $doc): bool => ($doc['category'] ?? '') === $cat['id']
                ));
            }

            $result[] = [
                'id'    => $cat['id'],
                'count' => $count,
                'label' => sprintf('%s (%d)', $cat['label'], $count),
            ];
        }

        return $result;
    }

    /**
     * Resuelve la etiqueta de insignia (badge) según la categoría y especialidad del médico
     * @param array<string, mixed> $doctor
     */
    public static function getCategoryBadgeLabel(array $doctor): string
    {
        $category = (string) ($doctor['category'] ?? '');
        $specialty = (string) ($doctor['specialty'] ?? '');

        if ($category === 'cirugia') {
            return 'Cirugía';
        }
        if ($category === 'ginecologia') {
            return 'Ginecología';
        }
        if ($category === 'traumatologia') {
            return 'Traumatología';
        }
        if (str_contains($specialty, 'Cardiólogo')) {
            return 'Cardiología';
        }
        if (str_contains($specialty, 'Gastroenterólogo')) {
            return 'Gastroenterología';
        }
        if (str_contains($specialty, 'Neurólogo')) {
            return 'Neurología';
        }
        if (str_contains($specialty, 'Urólogo')) {
            return 'Urología';
        }
        if (str_contains($specialty, 'Otorrino')) {
            return 'Otorrinolaringología';
        }
        if (str_contains($specialty, 'Psicólogo')) {
            return 'Psicología';
        }

        return 'Medicina interna';
    }

    /**
     * Obtiene los médicos activos con sus especialidades vinculadas para el formulario de citas
     * @return array<int, array<string, mixed>>
     */
    public static function getAllForBooking(): array
    {
        if (self::$bookingCache === null) {
            $specMap = [];
            foreach (Database::fetchAll(
                'SELECT ds.doctor_id, s.slug
                   FROM doctor_specialties ds
                   JOIN specialties s ON s.id = ds.specialty_id
               ORDER BY ds.is_primary DESC, s.sort_order ASC'
            ) as $rel) {
                $specMap[(int) $rel['doctor_id']][] = (string) $rel['slug'];
            }

            $rows = Database::fetchAll(
                'SELECT d.id, d.slug, d.name, d.specialty_label, d.category, d.accredited,
                        ps.slug AS primary_specialty_slug
                   FROM doctors d
              LEFT JOIN specialties ps ON ps.id = d.specialty_id
                  WHERE d.is_active = :active
               ORDER BY d.sort_order ASC, d.id ASC',
                ['active' => 1]
            );

            $result = [];
            foreach ($rows as $r) {
                $docId = (int) $r['id'];
                $result[] = [
                    'db_id'                  => $docId,
                    'id'                     => (string) $r['slug'],
                    'name'                   => (string) $r['name'],
                    'specialty'              => (string) $r['specialty_label'],
                    'category'               => (string) $r['category'],
                    'accredited'             => (bool) $r['accredited'],
                    'primary_specialty_slug' => $r['primary_specialty_slug'] !== null ? (string) $r['primary_specialty_slug'] : null,
                    'specialty_slugs'        => $specMap[$docId] ?? [],
                ];
            }
            self::$bookingCache = $result;
        }

        return self::$bookingCache;
    }

    /**
     * Busca un médico para agendamiento por slug exacto o por nombre (?doctor=)
     * @return array<string, mixed>|null
     */
    public static function findForBooking(string $slugOrName): ?array
    {
        $needle = trim($slugOrName);
        if ($needle === '') {
            return null;
        }

        $doctors = self::getAllForBooking();

        foreach ($doctors as $doc) {
            if (strcasecmp((string) $doc['id'], $needle) === 0 || strcasecmp((string) $doc['name'], $needle) === 0) {
                return $doc;
            }
        }

        foreach ($doctors as $doc) {
            if (stripos((string) $doc['name'], $needle) !== false) {
                return $doc;
            }
        }

        return null;
    }

    /**
     * Listado completo (activos e inactivos) para el panel administrativo.
     * @return array<int, array<string, mixed>>
     */
    public static function adminGetAll(): array
    {
        $extraMap = [];
        foreach (Database::fetchAll(
            'SELECT ds.doctor_id, s.name
               FROM doctor_specialties ds
               JOIN specialties s ON s.id = ds.specialty_id
              WHERE ds.is_primary = 0
           ORDER BY s.sort_order ASC'
        ) as $row) {
            $extraMap[(int) $row['doctor_id']][] = (string) $row['name'];
        }

        $rows = Database::fetchAll(
            'SELECT d.*, ps.name AS primary_specialty_name
               FROM doctors d
          LEFT JOIN specialties ps ON ps.id = d.specialty_id
           ORDER BY d.sort_order ASC, d.id ASC'
        );

        foreach ($rows as &$r) {
            $r['secondary_specialty_names'] = $extraMap[(int) $r['id']] ?? [];
        }
        unset($r);

        return $rows;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function adminFindById(int $id): ?array
    {
        $rows = Database::fetchAll('SELECT * FROM doctors WHERE id = :id LIMIT 1', ['id' => $id]);
        if ($rows === []) {
            return null;
        }
        $doc = $rows[0];
        $secRows = Database::fetchAll(
            'SELECT specialty_id FROM doctor_specialties WHERE doctor_id = :id AND is_primary = 0',
            ['id' => $id]
        );
        $doc['secondary_specialty_ids'] = array_map(static fn(array $s): int => (int) $s['specialty_id'], $secRows);
        return $doc;
    }

    public static function slugExists(string $slug, ?int $excludeId = null): bool
    {
        if ($excludeId !== null) {
            $rows = Database::fetchAll(
                'SELECT id FROM doctors WHERE slug = :slug AND id <> :id LIMIT 1',
                ['slug' => $slug, 'id' => $excludeId]
            );
        } else {
            $rows = Database::fetchAll(
                'SELECT id FROM doctors WHERE slug = :slug LIMIT 1',
                ['slug' => $slug]
            );
        }
        return $rows !== [];
    }

    /**
     * Crea o actualiza un médico y sincroniza su tabla puente doctor_specialties.
     *
     * @param array<string, mixed> $data
     * @param array<int, int>      $secondarySpecialtyIds
     */
    public static function adminSave(?int $id, array $data, array $secondarySpecialtyIds = []): int
    {
        $pdo = Database::getConnection();

        try {
            $pdo->beginTransaction();

            $params = [
                'slug'            => $data['slug'],
                'name'            => $data['name'],
                'specialty_label' => $data['specialty_label'],
                'category'        => $data['category'],
                'initials'        => $data['initials'],
                'location'        => $data['location'],
                'accredited'      => $data['accredited'] ? 1 : 0,
                'description'     => $data['description'],
                'specialty_id'    => $data['specialty_id'],
                'sort_order'      => $data['sort_order'],
                'is_active'       => $data['is_active'] ? 1 : 0,
            ];

            if ($id !== null && $id > 0) {
                $params['id'] = $id;
                $stmt = $pdo->prepare(
                    'UPDATE doctors
                        SET slug = :slug, name = :name, specialty_label = :specialty_label,
                            category = :category, initials = :initials, location = :location,
                            accredited = :accredited, description = :description,
                            specialty_id = :specialty_id, sort_order = :sort_order, is_active = :is_active
                      WHERE id = :id'
                );
                $stmt->execute($params);
                $doctorId = $id;
            } else {
                $stmt = $pdo->prepare(
                    'INSERT INTO doctors (
                        slug, name, specialty_label, category, initials, location,
                        accredited, description, specialty_id, sort_order, is_active
                    ) VALUES (
                        :slug, :name, :specialty_label, :category, :initials, :location,
                        :accredited, :description, :specialty_id, :sort_order, :is_active
                    )'
                );
                $stmt->execute($params);
                $doctorId = (int) $pdo->lastInsertId();
            }

            // Reconstruir relaciones en doctor_specialties
            $delStmt = $pdo->prepare('DELETE FROM doctor_specialties WHERE doctor_id = :id');
            $delStmt->execute(['id' => $doctorId]);

            $insRel = $pdo->prepare(
                'INSERT INTO doctor_specialties (doctor_id, specialty_id, is_primary)
                 VALUES (:doc_id, :spec_id, :is_primary)'
            );

            $primaryId = $data['specialty_id'] !== null ? (int) $data['specialty_id'] : null;
            if ($primaryId !== null && $primaryId > 0) {
                $insRel->execute([
                    'doc_id'     => $doctorId,
                    'spec_id'    => $primaryId,
                    'is_primary' => 1,
                ]);
            }

            foreach (array_unique($secondarySpecialtyIds) as $secId) {
                $secId = (int) $secId;
                if ($secId > 0 && $secId !== $primaryId) {
                    $insRel->execute([
                        'doc_id'     => $doctorId,
                        'spec_id'    => $secId,
                        'is_primary' => 0,
                    ]);
                }
            }

            $pdo->commit();
            self::$cache = null;
            self::$bookingCache = null;

            return $doctorId;
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('[HSM][DOCTOR_ADMIN] ' . $e->getMessage());
            throw new DataSourceException('No se pudo guardar el registro del médico.');
        }
    }

    public static function adminToggleActive(int $id): void
    {
        Database::execute('UPDATE doctors SET is_active = IF(is_active = 1, 0, 1) WHERE id = :id', ['id' => $id]);
        self::$cache = null;
        self::$bookingCache = null;
    }

    public static function adminDelete(int $id): void
    {
        Database::execute('DELETE FROM doctors WHERE id = :id', ['id' => $id]);
        self::$cache = null;
        self::$bookingCache = null;
    }
}
