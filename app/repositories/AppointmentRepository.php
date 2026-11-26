<?php
declare(strict_types=1);

/**
 * Repositorio PDO para persistencia de solicitudes de citas médicas (appointments)
 */

if (!defined('HSM_APP')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

class AppointmentRepository
{
    /**
     * Inserta una nueva solicitud de cita generando un código único HCSM-XXXX en transacción.
     *
     * @param array<string, mixed> $data
     * @return array{id: int, code: string}
     */
    public static function create(array $data): array
    {
        $pdo = Database::getConnection();

        for ($attempt = 0; $attempt < 12; $attempt++) {
            $code = Security::generateCode('HCSM', 4);

            try {
                $pdo->beginTransaction();

                $stmt = $pdo->prepare(
                    'INSERT INTO appointments (
                        code, attention_type, specialty_id, attention_label,
                        doctor_id, doctor_label,
                        patient_doc_type, patient_id_doc, patient_name,
                        patient_phone, patient_email,
                        appointment_date, shift, reason,
                        status, privacy_accepted_at, ip_hash, email_status
                    ) VALUES (
                        :code, :attention_type, :specialty_id, :attention_label,
                        :doctor_id, :doctor_label,
                        :patient_doc_type, :patient_id_doc, :patient_name,
                        :patient_phone, :patient_email,
                        :appointment_date, :shift, :reason,
                        :status, NOW(), :ip_hash, :email_status
                    )'
                );

                $stmt->execute([
                    'code'             => $code,
                    'attention_type'   => $data['attention_type'],
                    'specialty_id'     => $data['specialty_id'],
                    'attention_label'  => $data['attention_label'],
                    'doctor_id'        => $data['doctor_id'],
                    'doctor_label'     => $data['doctor_label'],
                    'patient_doc_type' => $data['patient_doc_type'],
                    'patient_id_doc'   => $data['patient_id_doc'],
                    'patient_name'     => $data['patient_name'],
                    'patient_phone'    => $data['patient_phone'],
                    'patient_email'    => $data['patient_email'],
                    'appointment_date' => $data['appointment_date'],
                    'shift'            => $data['shift'],
                    'reason'           => $data['reason'],
                    'status'           => 'pendiente',
                    'ip_hash'          => $data['ip_hash'],
                    'email_status'     => 'pendiente',
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

                // Código duplicado (SQLSTATE 23000): reintentar con un nuevo código aleatorio
                if ((string) $e->getCode() === '23000' && str_contains($e->getMessage(), 'uq_appointments_code')) {
                    continue;
                }

                error_log('[HSM][APPOINTMENT_DB] ' . $e->getMessage());
                throw new DataSourceException('No se pudo registrar la solicitud de cita en este momento.');
            }
        }

        error_log('[HSM][APPOINTMENT_DB] Agotados los reintentos de generación de código HCSM-XXXX.');
        throw new DataSourceException('No se pudo generar el código de reserva. Inténtalo nuevamente.');
    }

    public static function updateEmailStatus(int $id, string $status): void
    {
        try {
            if ($status === 'enviado') {
                Database::execute(
                    'UPDATE appointments SET email_status = :status, email_sent_at = NOW() WHERE id = :id',
                    ['status' => 'enviado', 'id' => $id]
                );
            } else {
                Database::execute(
                    'UPDATE appointments SET email_status = :status WHERE id = :id',
                    ['status' => 'error', 'id' => $id]
                );
            }
        } catch (Throwable $e) {
            error_log('[HSM][APPOINTMENT_DB] Error actualizando email_status: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene contadores por estado para el dashboard y pestañas de filtro.
     *
     * @return array{all: int, pendiente: int, confirmada: int, cancelada: int, atendida: int, today_pending: int}
     */
    public static function getStatusCounts(): array
    {
        $counts = [
            'all'           => 0,
            'pendiente'     => 0,
            'confirmada'    => 0,
            'cancelada'     => 0,
            'atendida'      => 0,
            'today_pending' => 0,
        ];

        foreach (Database::fetchAll('SELECT status, COUNT(*) AS total FROM appointments GROUP BY status') as $row) {
            $st = (string) $row['status'];
            $n  = (int) $row['total'];
            if (isset($counts[$st])) {
                $counts[$st] = $n;
            }
            $counts['all'] += $n;
        }

        $today = (new DateTimeImmutable('today', new DateTimeZone('America/Guayaquil')))->format('Y-m-d');
        $todayRows = Database::fetchAll(
            'SELECT COUNT(*) AS total FROM appointments WHERE status = :st AND appointment_date = :dt',
            ['st' => 'pendiente', 'dt' => $today]
        );
        $counts['today_pending'] = (int) ($todayRows[0]['total'] ?? 0);

        return $counts;
    }

    /**
     * Lista paginada y filtrada de citas médicas para el panel administrativo.
     *
     * @param array<string, string> $filters
     * @return array{items: array<int, array<string, mixed>>, total: int, page: int, perPage: int, totalPages: int}
     */
    public static function paginate(array $filters = [], int $page = 1, int $perPage = 15): array
    {
        $where  = [];
        $params = [];

        $status = trim((string) ($filters['status'] ?? ''));
        if (in_array($status, ['pendiente', 'confirmada', 'cancelada', 'atendida'], true)) {
            $where[]          = 'status = :status';
            $params['status'] = $status;
        }

        $date = trim((string) ($filters['date'] ?? ''));
        if ($date !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1) {
            $where[]        = 'appointment_date = :dt';
            $params['dt']   = $date;
        }

        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '') {
            $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q);
            $where[] = '(code LIKE :q1 OR patient_id_doc LIKE :q2 OR patient_name LIKE :q3 OR patient_phone LIKE :q4 OR attention_label LIKE :q5)';
            $like = '%' . $escaped . '%';
            $params['q1'] = $like;
            $params['q2'] = $like;
            $params['q3'] = $like;
            $params['q4'] = $like;
            $params['q5'] = $like;
        }

        $whereSql = $where !== [] ? (' WHERE ' . implode(' AND ', $where)) : '';

        $countRows  = Database::fetchAll('SELECT COUNT(*) AS total FROM appointments' . $whereSql, $params);
        $total      = (int) ($countRows[0]['total'] ?? 0);
        $totalPages = max(1, (int) ceil($total / max(1, $perPage)));
        $safePage   = max(1, min($page, $totalPages));
        $offset     = ($safePage - 1) * $perPage;

        try {
            $pdo  = Database::getConnection();
            $stmt = $pdo->prepare(
                'SELECT id, code, attention_type, attention_label, doctor_label,
                        patient_doc_type, patient_id_doc, patient_name, patient_phone, patient_email,
                        appointment_date, shift, confirmed_time, status, email_status, created_at
                   FROM appointments'
                . $whereSql
                . ' ORDER BY FIELD(status, \'pendiente\', \'confirmada\', \'atendida\', \'cancelada\'), appointment_date ASC, id DESC
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
            error_log('[HSM][APPOINTMENT_DB] ' . $e->getMessage());
            throw new DataSourceException('No se pudo consultar el listado de citas.');
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function getRecent(int $limit = 5): array
    {
        try {
            $stmt = Database::getConnection()->prepare(
                'SELECT id, code, attention_label, doctor_label, patient_name, patient_phone,
                        appointment_date, shift, confirmed_time, status, created_at
                   FROM appointments
               ORDER BY FIELD(status, \'pendiente\', \'confirmada\', \'atendida\', \'cancelada\'), created_at DESC
                  LIMIT :lim'
            );
            $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('[HSM][APPOINTMENT_DB] ' . $e->getMessage());
            throw new DataSourceException('No se pudieron obtener las citas recientes.');
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function findById(int $id): ?array
    {
        $rows = Database::fetchAll('SELECT * FROM appointments WHERE id = :id LIMIT 1', ['id' => $id]);
        return $rows[0] ?? null;
    }

    public static function updateManagement(int $id, string $status, ?string $confirmedTime, ?string $adminNotes): void
    {
        Database::execute(
            'UPDATE appointments
                SET status = :status,
                    confirmed_time = :confirmed_time,
                    admin_notes = :admin_notes
              WHERE id = :id',
            [
                'status'         => $status,
                'confirmed_time' => $confirmedTime,
                'admin_notes'    => $adminNotes,
                'id'             => $id,
            ]
        );
    }
}
