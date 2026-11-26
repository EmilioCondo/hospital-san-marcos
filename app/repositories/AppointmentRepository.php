<?php
declare(strict_types=1);

/**
 * Repositorio PDO para persistencia de solicitudes de citas médicas (tabla `citas`)
 * Vinculado exclusivamente a `especialidades` y `medicos`.
 */

if (!defined('HSM_APP')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

class AppointmentRepository
{
    private const SELECT_MAPPED = 'SELECT
        id,
        codigo AS code,
        especialidad_id AS specialty_id,
        nombre_especialidad AS attention_label,
        medico_id AS doctor_id,
        nombre_medico AS doctor_label,
        tipo_documento_paciente AS patient_doc_type,
        documento_paciente AS patient_id_doc,
        nombre_paciente AS patient_name,
        telefono_paciente AS patient_phone,
        correo_paciente AS patient_email,
        fecha_cita AS appointment_date,
        turno AS shift,
        hora_confirmada AS confirmed_time,
        motivo AS reason,
        notas_admin AS admin_notes,
        estado AS status,
        estado_correo AS email_status,
        correo_enviado_en AS email_sent_at,
        creado_en AS created_at,
        actualizado_en AS updated_at
      FROM citas';

    /**
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
                    'INSERT INTO citas (
                        codigo, especialidad_id, nombre_especialidad,
                        medico_id, nombre_medico,
                        tipo_documento_paciente, documento_paciente, nombre_paciente,
                        telefono_paciente, correo_paciente,
                        fecha_cita, turno, motivo,
                        estado, privacidad_aceptada_en, hash_ip, estado_correo
                    ) VALUES (
                        :codigo, :especialidad_id, :nombre_especialidad,
                        :medico_id, :nombre_medico,
                        :tipo_doc, :documento, :nombre_paciente,
                        :telefono, :correo,
                        :fecha_cita, :turno, :motivo,
                        :estado, NOW(), :hash_ip, :estado_correo
                    )'
                );

                $stmt->execute([
                    'codigo'              => $code,
                    'especialidad_id'     => $data['specialty_id'],
                    'nombre_especialidad' => $data['attention_label'],
                    'medico_id'           => $data['doctor_id'],
                    'nombre_medico'       => $data['doctor_label'],
                    'tipo_doc'            => $data['patient_doc_type'],
                    'documento'           => $data['patient_id_doc'],
                    'nombre_paciente'     => $data['patient_name'],
                    'telefono'            => $data['patient_phone'],
                    'correo'              => $data['patient_email'],
                    'fecha_cita'          => $data['appointment_date'],
                    'turno'               => $data['shift'],
                    'motivo'              => $data['reason'],
                    'estado'              => 'pendiente',
                    'hash_ip'             => $data['ip_hash'],
                    'estado_correo'       => 'pendiente',
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

                if ((string) $e->getCode() === '23000' && str_contains($e->getMessage(), 'uq_citas_codigo')) {
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
                    'UPDATE citas SET estado_correo = :estado, correo_enviado_en = NOW() WHERE id = :id',
                    ['estado' => 'enviado', 'id' => $id]
                );
            } else {
                Database::execute(
                    'UPDATE citas SET estado_correo = :estado WHERE id = :id',
                    ['estado' => 'error', 'id' => $id]
                );
            }
        } catch (Throwable $e) {
            error_log('[HSM][APPOINTMENT_DB] Error actualizando estado_correo: ' . $e->getMessage());
        }
    }

    /**
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

        foreach (Database::fetchAll('SELECT estado, COUNT(*) AS total FROM citas GROUP BY estado') as $row) {
            $st = (string) $row['estado'];
            $n  = (int) $row['total'];
            if (isset($counts[$st])) {
                $counts[$st] = $n;
            }
            $counts['all'] += $n;
        }

        $today = (new DateTimeImmutable('today', new DateTimeZone('America/Guayaquil')))->format('Y-m-d');
        $todayRows = Database::fetchAll(
            'SELECT COUNT(*) AS total FROM citas WHERE estado = :st AND fecha_cita = :dt',
            ['st' => 'pendiente', 'dt' => $today]
        );
        $counts['today_pending'] = (int) ($todayRows[0]['total'] ?? 0);

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
        if (in_array($status, ['pendiente', 'confirmada', 'cancelada', 'atendida'], true)) {
            $where[]          = 'estado = :status';
            $params['status'] = $status;
        }

        $date = trim((string) ($filters['date'] ?? ''));
        if ($date !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1) {
            $where[]      = 'fecha_cita = :dt';
            $params['dt'] = $date;
        }

        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '') {
            $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q);
            $where[] = '(codigo LIKE :q1 OR documento_paciente LIKE :q2 OR nombre_paciente LIKE :q3 OR telefono_paciente LIKE :q4 OR nombre_especialidad LIKE :q5)';
            $like = '%' . $escaped . '%';
            $params['q1'] = $like;
            $params['q2'] = $like;
            $params['q3'] = $like;
            $params['q4'] = $like;
            $params['q5'] = $like;
        }

        $whereSql = $where !== [] ? (' WHERE ' . implode(' AND ', $where)) : '';

        $countRows  = Database::fetchAll('SELECT COUNT(*) AS total FROM citas' . $whereSql, $params);
        $total      = (int) ($countRows[0]['total'] ?? 0);
        $totalPages = max(1, (int) ceil($total / max(1, $perPage)));
        $safePage   = max(1, min($page, $totalPages));
        $offset     = ($safePage - 1) * $perPage;

        try {
            $stmt = Database::getConnection()->prepare(
                self::SELECT_MAPPED
                . $whereSql
                . ' ORDER BY FIELD(estado, \'pendiente\', \'confirmada\', \'atendida\', \'cancelada\'), fecha_cita ASC, id DESC
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
                self::SELECT_MAPPED
                . ' ORDER BY FIELD(estado, \'pendiente\', \'confirmada\', \'atendida\', \'cancelada\'), creado_en DESC
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
        $rows = Database::fetchAll(self::SELECT_MAPPED . ' WHERE id = :id LIMIT 1', ['id' => $id]);
        return $rows[0] ?? null;
    }

    public static function updateManagement(int $id, string $status, ?string $confirmedTime, ?string $adminNotes): void
    {
        Database::execute(
            'UPDATE citas
                SET estado = :estado,
                    hora_confirmada = :hora_confirmada,
                    notas_admin = :notas_admin
              WHERE id = :id',
            [
                'estado'          => $status,
                'hora_confirmada' => $confirmedTime,
                'notas_admin'     => $adminNotes,
                'id'              => $id,
            ]
        );
    }
}
