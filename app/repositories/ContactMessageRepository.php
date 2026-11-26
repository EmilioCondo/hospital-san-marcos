<?php
declare(strict_types=1);

/**
 * Repositorio PDO para persistencia de mensajes de contacto (tabla `mensajes_contacto`)
 */

if (!defined('HSM_APP')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

class ContactMessageRepository
{
    private const SELECT_MAPPED = 'SELECT
        id,
        codigo AS code,
        nombre AS name,
        correo AS email,
        telefono AS phone,
        asunto AS subject,
        etiqueta_asunto AS subject_label,
        mensaje AS message,
        estado AS status,
        estado_correo AS email_status,
        correo_enviado_en AS email_sent_at,
        creado_en AS created_at
      FROM mensajes_contacto';

    /**
     * @param array<string, mixed> $data
     * @return array{id: int, code: string}
     */
    public static function create(array $data): array
    {
        $pdo = Database::getConnection();

        for ($attempt = 0; $attempt < 12; $attempt++) {
            $code = Security::generateCode('CONT', 4);

            try {
                $pdo->beginTransaction();

                $stmt = $pdo->prepare(
                    'INSERT INTO mensajes_contacto (
                        codigo, nombre, correo, telefono,
                        asunto, etiqueta_asunto, mensaje,
                        estado, privacidad_aceptada_en, hash_ip, estado_correo
                    ) VALUES (
                        :codigo, :nombre, :correo, :telefono,
                        :asunto, :etiqueta_asunto, :mensaje,
                        :estado, NOW(), :hash_ip, :estado_correo
                    )'
                );

                $stmt->execute([
                    'codigo'          => $code,
                    'nombre'          => $data['name'],
                    'correo'          => $data['email'],
                    'telefono'        => $data['phone'],
                    'asunto'          => $data['subject'],
                    'etiqueta_asunto' => $data['subject_label'],
                    'mensaje'         => $data['message'],
                    'estado'          => 'nuevo',
                    'hash_ip'         => $data['ip_hash'],
                    'estado_correo'   => 'pendiente',
                ]);

                $insertId = (int) $pdo->lastInsertId();
                $pdo->commit();

                return [
                    'id'   => $insertId,
                    'code' => $code,
                ];
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                if ((string) $e->getCode() === '23000' && str_contains($e->getMessage(), 'uq_contacto_codigo')) {
                    continue;
                }

                error_log('[HSM][CONTACT_DB] ' . $e->getMessage());
                throw new DataSourceException('No se pudo registrar tu mensaje en este momento.');
            }
        }

        error_log('[HSM][CONTACT_DB] Agotados los reintentos de generación de código CONT-XXXX.');
        throw new DataSourceException('No se pudo generar el código de seguimiento. Inténtalo nuevamente.');
    }

    public static function updateEmailStatus(int $id, string $status): void
    {
        try {
            if ($status === 'enviado') {
                Database::execute(
                    'UPDATE mensajes_contacto SET estado_correo = :estado, correo_enviado_en = NOW() WHERE id = :id',
                    ['estado' => 'enviado', 'id' => $id]
                );
            } else {
                Database::execute(
                    'UPDATE mensajes_contacto SET estado_correo = :estado WHERE id = :id',
                    ['estado' => 'error', 'id' => $id]
                );
            }
        } catch (Throwable $e) {
            error_log('[HSM][CONTACT_DB] Error actualizando estado_correo: ' . $e->getMessage());
        }
    }

    /**
     * @return array{all: int, nuevo: int, leido: int, respondido: int}
     */
    public static function getStatusCounts(): array
    {
        $counts = [
            'all'        => 0,
            'nuevo'      => 0,
            'leido'      => 0,
            'respondido' => 0,
        ];

        foreach (Database::fetchAll('SELECT estado, COUNT(*) AS total FROM mensajes_contacto GROUP BY estado') as $row) {
            $st = (string) $row['estado'];
            $n  = (int) $row['total'];
            if (isset($counts[$st])) {
                $counts[$st] = $n;
            }
            $counts['all'] += $n;
        }

        return $counts;
    }

    /**
     * @param array<string, string> $filters
     * @return array{items: array<int, array<string, mixed>>, total: int, page: int, perPage: int, totalPages: int}
     */
    public static function paginate(array $filters = [], int $page = 1, int $perPage = 15): array
    {
        $where  = [];
        $params = [];

        $status = trim((string) ($filters['status'] ?? ''));
        if (in_array($status, ['nuevo', 'leido', 'respondido'], true)) {
            $where[]          = 'estado = :status';
            $params['status'] = $status;
        }

        $subject = trim((string) ($filters['subject'] ?? ''));
        if ($subject !== '' && isset(ContactValidator::SUBJECTS[$subject])) {
            $where[]           = 'asunto = :subject';
            $params['subject'] = $subject;
        }

        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '') {
            $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q);
            $where[] = '(codigo LIKE :q1 OR nombre LIKE :q2 OR correo LIKE :q3 OR telefono LIKE :q4)';
            $like = '%' . $escaped . '%';
            $params['q1'] = $like;
            $params['q2'] = $like;
            $params['q3'] = $like;
            $params['q4'] = $like;
        }

        $whereSql = $where !== [] ? (' WHERE ' . implode(' AND ', $where)) : '';

        $countRows  = Database::fetchAll('SELECT COUNT(*) AS total FROM mensajes_contacto' . $whereSql, $params);
        $total      = (int) ($countRows[0]['total'] ?? 0);
        $totalPages = max(1, (int) ceil($total / max(1, $perPage)));
        $safePage   = max(1, min($page, $totalPages));
        $offset     = ($safePage - 1) * $perPage;

        try {
            $stmt = Database::getConnection()->prepare(
                self::SELECT_MAPPED
                . $whereSql
                . ' ORDER BY FIELD(estado, \'nuevo\', \'leido\', \'respondido\'), id DESC
                    LIMIT :lim OFFSET :off'
            );

            foreach ($params as $key => $val) {
                $stmt->bindValue(':' . $key, $val);
            }
            $stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
            $stmt->execute();

            return [
                'items'      => $stmt->fetchAll(),
                'total'      => $total,
                'page'       => $safePage,
                'perPage'    => $perPage,
                'totalPages' => $totalPages,
            ];
        } catch (PDOException $e) {
            error_log('[HSM][CONTACT_DB] ' . $e->getMessage());
            throw new DataSourceException('No se pudo consultar la bandeja de mensajes.');
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function getRecent(int $limit = 5): array
    {
        try {
            $stmt = Database::getConnection()->prepare(
                self::SELECT_MAPPED
                . ' ORDER BY FIELD(estado, \'nuevo\', \'leido\', \'respondido\'), id DESC
                    LIMIT :lim'
            );
            $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('[HSM][CONTACT_DB] ' . $e->getMessage());
            throw new DataSourceException('No se pudieron obtener los mensajes recientes.');
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function findById(int $id): ?array
    {
        $rows = Database::fetchAll(self::SELECT_MAPPED . ' WHERE id = :id LIMIT 1', ['id' => $id]);
        return $rows[0] ?? null;
    }

    public static function updateStatus(int $id, string $status): void
    {
        if (!in_array($status, ['nuevo', 'leido', 'respondido'], true)) {
            return;
        }
        Database::execute(
            'UPDATE mensajes_contacto SET estado = :estado WHERE id = :id',
            ['estado' => $status, 'id' => $id]
        );
    }
}
