<?php
declare(strict_types=1);

/**
 * Repositorio PDO de Médicos (tabla `medicos` y `medico_especialidades`)
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
     * Obtiene la nómina activa de médicos (el nombre ya incluye Dr. / Dra.)
     * @return array<int, array<string, mixed>>
     */
    public static function getAll(): array
    {
        if (self::$cache === null) {
            $rows = Database::fetchAll(
                'SELECT slug, nombre, titulo_especialidad, categoria, iniciales, ubicacion, acreditado, descripcion
                   FROM medicos
                  WHERE activo = :activo
               ORDER BY orden ASC, id ASC',
                ['activo' => 1]
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
            'SELECT slug, nombre, titulo_especialidad, categoria, iniciales, ubicacion, acreditado, descripcion
               FROM medicos
              WHERE slug = :slug AND activo = :activo
              LIMIT 1',
            ['slug' => $id, 'activo' => 1]
        );
        return $rows === [] ? null : self::mapRow($rows[0]);
    }

    /**
     * @param array<string, mixed> $r
     * @return array<string, mixed>
     */
    private static function mapRow(array $r): array
    {
        return [
            'id'          => (string) $r['slug'],
            'name'        => (string) $r['nombre'],
            'specialty'   => (string) $r['titulo_especialidad'],
            'category'    => (string) $r['categoria'],
            'initials'    => (string) $r['iniciales'],
            'location'    => (string) $r['ubicacion'],
            'accredited'  => (bool) $r['acreditado'],
            'description' => (string) $r['descripcion'],
        ];
    }

    /**
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
     * @param array<string, mixed> $doctor
     */
    public static function getCategoryBadgeLabel(array $doctor): string
    {
        $category  = (string) ($doctor['category'] ?? '');
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
                'SELECT me.medico_id, e.slug
                   FROM medico_especialidades me
                   JOIN especialidades e ON e.id = me.especialidad_id
               ORDER BY me.es_principal DESC, e.orden ASC'
            ) as $rel) {
                $specMap[(int) $rel['medico_id']][] = (string) $rel['slug'];
            }

            $rows = Database::fetchAll(
                'SELECT m.id, m.slug, m.nombre, m.titulo_especialidad, m.categoria, m.acreditado,
                        ep.slug AS primary_specialty_slug
                   FROM medicos m
              LEFT JOIN especialidades ep ON ep.id = m.especialidad_id
                  WHERE m.activo = :activo
               ORDER BY m.orden ASC, m.id ASC',
                ['activo' => 1]
            );

            $result = [];
            foreach ($rows as $r) {
                $docId = (int) $r['id'];
                $result[] = [
                    'db_id'                  => $docId,
                    'id'                     => (string) $r['slug'],
                    'name'                   => (string) $r['nombre'],
                    'specialty'              => (string) $r['titulo_especialidad'],
                    'category'               => (string) $r['categoria'],
                    'accredited'             => (bool) $r['acreditado'],
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

        // Permitir coincidencia con o sin prefijo Dr./Dra. en ?doctor=
        $strippedNeedle = trim((string) preg_replace('/^(dr\.?|dra\.?)\s+/iu', '', $needle));
        $doctors = self::getAllForBooking();

        foreach ($doctors as $doc) {
            if (
                strcasecmp((string) $doc['id'], $needle) === 0
                || strcasecmp((string) $doc['name'], $needle) === 0
            ) {
                return $doc;
            }
        }

        foreach ($doctors as $doc) {
            if (
                stripos((string) $doc['name'], $needle) !== false
                || ($strippedNeedle !== '' && stripos((string) $doc['name'], $strippedNeedle) !== false)
            ) {
                return $doc;
            }
        }

        return null;
    }

    /**
     * Listado completo (activos e inactivos) para /admin/medicos
     * @return array<int, array<string, mixed>>
     */
    public static function adminGetAll(): array
    {
        $extraMap = [];
        foreach (Database::fetchAll(
            'SELECT me.medico_id, e.nombre
               FROM medico_especialidades me
               JOIN especialidades e ON e.id = me.especialidad_id
              WHERE me.es_principal = 0
           ORDER BY e.orden ASC'
        ) as $row) {
            $extraMap[(int) $row['medico_id']][] = (string) $row['nombre'];
        }

        $rows = Database::fetchAll(
            'SELECT m.id, m.slug, m.nombre AS name, m.titulo_especialidad AS specialty_label,
                    m.categoria AS category, m.iniciales AS initials, m.ubicacion AS location,
                    m.acreditado AS accredited, m.descripcion AS description,
                    m.especialidad_id AS specialty_id, m.orden AS sort_order, m.activo AS is_active,
                    ep.nombre AS primary_specialty_name
               FROM medicos m
          LEFT JOIN especialidades ep ON ep.id = m.especialidad_id
           ORDER BY m.orden ASC, m.id ASC'
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
        $rows = Database::fetchAll(
            'SELECT id, slug, nombre AS name, titulo_especialidad AS specialty_label,
                    categoria AS category, iniciales AS initials, ubicacion AS location,
                    acreditado AS accredited, descripcion AS description,
                    especialidad_id AS specialty_id, orden AS sort_order, activo AS is_active
               FROM medicos
              WHERE id = :id
              LIMIT 1',
            ['id' => $id]
        );
        if ($rows === []) {
            return null;
        }
        $doc = $rows[0];
        $secRows = Database::fetchAll(
            'SELECT especialidad_id FROM medico_especialidades WHERE medico_id = :id AND es_principal = 0',
            ['id' => $id]
        );
        $doc['secondary_specialty_ids'] = array_map(static fn(array $s): int => (int) $s['especialidad_id'], $secRows);
        return $doc;
    }

    public static function slugExists(string $slug, ?int $excludeId = null): bool
    {
        if ($excludeId !== null) {
            $rows = Database::fetchAll(
                'SELECT id FROM medicos WHERE slug = :slug AND id <> :id LIMIT 1',
                ['slug' => $slug, 'id' => $excludeId]
            );
        } else {
            $rows = Database::fetchAll(
                'SELECT id FROM medicos WHERE slug = :slug LIMIT 1',
                ['slug' => $slug]
            );
        }
        return $rows !== [];
    }

    /**
     * @param array<string, mixed> $data
     * @param array<int, int>      $secondarySpecialtyIds
     */
    public static function adminSave(?int $id, array $data, array $secondarySpecialtyIds = []): int
    {
        $pdo = Database::getConnection();

        try {
            $pdo->beginTransaction();

            $params = [
                'slug'                => $data['slug'],
                'nombre'              => $data['name'],
                'titulo_especialidad' => $data['specialty_label'],
                'categoria'           => $data['category'],
                'iniciales'           => $data['initials'],
                'ubicacion'           => $data['location'],
                'acreditado'          => $data['accredited'] ? 1 : 0,
                'descripcion'         => $data['description'],
                'especialidad_id'     => $data['specialty_id'],
                'orden'               => $data['sort_order'],
                'activo'              => $data['is_active'] ? 1 : 0,
            ];

            if ($id !== null && $id > 0) {
                $params['id'] = $id;
                $pdo->prepare(
                    'UPDATE medicos
                        SET slug = :slug, nombre = :nombre, titulo_especialidad = :titulo_especialidad,
                            categoria = :categoria, iniciales = :iniciales, ubicacion = :ubicacion,
                            acreditado = :acreditado, descripcion = :descripcion,
                            especialidad_id = :especialidad_id, orden = :orden, activo = :activo
                      WHERE id = :id'
                )->execute($params);
                $doctorId = $id;
            } else {
                $pdo->prepare(
                    'INSERT INTO medicos (
                        slug, nombre, titulo_especialidad, categoria, iniciales, ubicacion,
                        acreditado, descripcion, especialidad_id, orden, activo
                    ) VALUES (
                        :slug, :nombre, :titulo_especialidad, :categoria, :iniciales, :ubicacion,
                        :acreditado, :descripcion, :especialidad_id, :orden, :activo
                    )'
                )->execute($params);
                $doctorId = (int) $pdo->lastInsertId();
            }

            $pdo->prepare('DELETE FROM medico_especialidades WHERE medico_id = :id')->execute(['id' => $doctorId]);

            $insRel = $pdo->prepare(
                'INSERT INTO medico_especialidades (medico_id, especialidad_id, es_principal)
                 VALUES (:med_id, :esp_id, :principal)'
            );

            $primaryId = $data['specialty_id'] !== null ? (int) $data['specialty_id'] : null;
            if ($primaryId !== null && $primaryId > 0) {
                $insRel->execute([
                    'med_id'    => $doctorId,
                    'esp_id'    => $primaryId,
                    'principal' => 1,
                ]);
            }

            foreach (array_unique($secondarySpecialtyIds) as $secId) {
                $secId = (int) $secId;
                if ($secId > 0 && $secId !== $primaryId) {
                    $insRel->execute([
                        'med_id'    => $doctorId,
                        'esp_id'    => $secId,
                        'principal' => 0,
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
        Database::execute('UPDATE medicos SET activo = IF(activo = 1, 0, 1) WHERE id = :id', ['id' => $id]);
        self::$cache = null;
        self::$bookingCache = null;
    }

    public static function adminDelete(int $id): void
    {
        Database::execute('DELETE FROM medicos WHERE id = :id', ['id' => $id]);
        self::$cache = null;
        self::$bookingCache = null;
    }
}
