<?php
declare(strict_types=1);

/**
 * Repositorio PDO para persistencia de mensajes de contacto (contact_messages)
 */

if (!defined('HSM_APP')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

class ContactMessageRepository
{
    /**
     * Inserta un nuevo mensaje de contacto generando un código único CONT-XXXX.
     *
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
                    'INSERT INTO contact_messages (
                        code, name, email, phone,
                        subject, subject_label, message,
                        status, privacy_accepted_at, ip_hash, email_status
                    ) VALUES (
                        :code, :name, :email, :phone,
                        :subject, :subject_label, :message,
                        :status, NOW(), :ip_hash, :email_status
                    )'
                );

                $stmt->execute([
                    'code'          => $code,
                    'name'          => $data['name'],
                    'email'         => $data['email'],
                    'phone'         => $data['phone'],
                    'subject'       => $data['subject'],
                    'subject_label' => $data['subject_label'],
                    'message'       => $data['message'],
                    'status'        => 'nuevo',
                    'ip_hash'       => $data['ip_hash'],
                    'email_status'  => 'pendiente',
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

                if ((string) $e->getCode() === '23000' && str_contains($e->getMessage(), 'uq_contact_code')) {
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
                    'UPDATE contact_messages SET email_status = :status, email_sent_at = NOW() WHERE id = :id',
                    ['status' => 'enviado', 'id' => $id]
                );
            } else {
                Database::execute(
                    'UPDATE contact_messages SET email_status = :status WHERE id = :id',
                    ['status' => 'error', 'id' => $id]
                );
            }
        } catch (Throwable $e) {
            error_log('[HSM][CONTACT_DB] Error actualizando email_status: ' . $e->getMessage());
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

        foreach (Database::fetchAll('SELECT status, COUNT(*) AS total FROM contact_messages GROUP BY status') as $row) {
            $st = (string) $row['status'];
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
            $where[]          = 'status = :status';
            $params['status'] = $status;
        }

        $subject = trim((string) ($filters['subject'] ?? ''));
        if ($subject !== '' && isset(ContactValidator::SUBJECTS[$subject])) {
            $where[]           = 'subject = :subject';
            $params['subject'] = $subject;
        }

        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '') {
            $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q);
            $where[] = '(code LIKE :q1 OR name LIKE :q2 OR email LIKE :q3 OR phone LIKE :q4)';
            $like = '%' . $escaped . '%';
            $params['q1'] = $like;
            $params['q2'] = $like;
            $params['q3'] = $like;
            $params['q4'] = $like;
        }

        $whereSql = $where !== [] ? (' WHERE ' . implode(' AND ', $where)) : '';

        $countRows  = Database::fetchAll('SELECT COUNT(*) AS total FROM contact_messages' . $whereSql, $params);
        $total      = (int) ($countRows[0]['total'] ?? 0);
        $totalPages = max(1, (int) ceil($total / max(1, $perPage)));
        $safePage   = max(1, min($page, $totalPages));
        $offset     = ($safePage - 1) * $perPage;

        try {
            $stmt = Database::getConnection()->prepare(
                'SELECT id, code, name, email, phone, subject, subject_label, message, status, email_status, created_at
                   FROM contact_messages'
                . $whereSql
                . ' ORDER BY FIELD(status, \'nuevo\', \'leido\', \'respondido\'), id DESC
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
                'SELECT id, code, name, email, subject_label, status, created_at
                   FROM contact_messages
               ORDER BY FIELD(status, \'nuevo\', \'leido\', \'respondido\'), id DESC
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
        $rows = Database::fetchAll('SELECT * FROM contact_messages WHERE id = :id LIMIT 1', ['id' => $id]);
        return $rows[0] ?? null;
    }

    public static function updateStatus(int $id, string $status): void
    {
        if (!in_array($status, ['nuevo', 'leido', 'respondido'], true)) {
            return;
        }
        Database::execute(
            'UPDATE contact_messages SET status = :status WHERE id = :id',
            ['status' => $status, 'id' => $id]
        );
    }
}
