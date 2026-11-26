<?php
declare(strict_types=1);

/**
 * Repositorio de Preguntas Frecuentes (FAQs)
 * Diseñado para desacoplar la fuente de datos (actualmente archivos PHP; preparado para MySQL/PDO en Etapa 2)
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
     * Obtiene las 5 preguntas frecuentes oficiales
     * @return array<int, array<string, string>>
     */
    public static function getAll(): array
    {
        if (self::$cache === null) {
            $rows = Database::fetchAll(
                'SELECT slug, question, answer FROM faqs WHERE is_active = :active ORDER BY sort_order ASC, id ASC',
                ['active' => 1]
            );
            self::$cache = array_map(static fn(array $r): array => self::mapRow($r), $rows);
        }
        return self::$cache;
    }

    /**
     * Busca una pregunta frecuente por su identificador (slug)
     * @return array<string, string>|null
     */
    public static function findById(string $id): ?array
    {
        $rows = Database::fetchAll(
            'SELECT slug, question, answer FROM faqs WHERE slug = :slug AND is_active = :active LIMIT 1',
            ['slug' => $id, 'active' => 1]
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
            'question' => (string) $r['question'],
            'answer'   => (string) $r['answer'],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function adminGetAll(): array
    {
        return Database::fetchAll('SELECT * FROM faqs ORDER BY sort_order ASC, id ASC');
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function adminFindById(int $id): ?array
    {
        $rows = Database::fetchAll('SELECT * FROM faqs WHERE id = :id LIMIT 1', ['id' => $id]);
        return $rows[0] ?? null;
    }

    public static function slugExists(string $slug, ?int $excludeId = null): bool
    {
        if ($excludeId !== null) {
            $rows = Database::fetchAll(
                'SELECT id FROM faqs WHERE slug = :slug AND id <> :id LIMIT 1',
                ['slug' => $slug, 'id' => $excludeId]
            );
        } else {
            $rows = Database::fetchAll(
                'SELECT id FROM faqs WHERE slug = :slug LIMIT 1',
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
            'slug'       => $data['slug'],
            'question'   => $data['question'],
            'answer'     => $data['answer'],
            'sort_order' => $data['sort_order'],
            'is_active'  => $data['is_active'] ? 1 : 0,
        ];

        if ($id !== null && $id > 0) {
            $params['id'] = $id;
            Database::execute(
                'UPDATE faqs
                    SET slug = :slug, question = :question, answer = :answer,
                        sort_order = :sort_order, is_active = :is_active
                  WHERE id = :id',
                $params
            );
            self::$cache = null;
            return $id;
        }

        Database::execute(
            'INSERT INTO faqs (slug, question, answer, sort_order, is_active)
             VALUES (:slug, :question, :answer, :sort_order, :is_active)',
            $params
        );
        self::$cache = null;
        return (int) Database::getConnection()->lastInsertId();
    }

    public static function adminToggleActive(int $id): void
    {
        Database::execute('UPDATE faqs SET is_active = IF(is_active = 1, 0, 1) WHERE id = :id', ['id' => $id]);
        self::$cache = null;
    }

    public static function adminDelete(int $id): void
    {
        Database::execute('DELETE FROM faqs WHERE id = :id', ['id' => $id]);
        self::$cache = null;
    }
}
