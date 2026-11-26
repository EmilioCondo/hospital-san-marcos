<?php
declare(strict_types=1);

/**
 * Repositorio PDO de usuarios administrativos (tabla `usuarios_admin`)
 */

if (!defined('HSM_APP')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

class AdminUserRepository
{
    /**
     * @return array<string, mixed>|null
     */
    public static function findByUsernameOrEmail(string $identifier): ?array
    {
        $needle = trim($identifier);
        if ($needle === '') {
            return null;
        }

        $rows = Database::fetchAll(
            'SELECT id, usuario AS username, correo AS email, clave_hash AS password_hash,
                    rol AS role, activo AS is_active, ultimo_acceso_en AS last_login_at, creado_en AS created_at
               FROM usuarios_admin
              WHERE usuario = :u OR correo = :e
              LIMIT 1',
            ['u' => $needle, 'e' => strtolower($needle)]
        );

        return $rows[0] ?? null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function findById(int $id): ?array
    {
        $rows = Database::fetchAll(
            'SELECT id, usuario AS username, correo AS email, rol AS role,
                    activo AS is_active, ultimo_acceso_en AS last_login_at, creado_en AS created_at
               FROM usuarios_admin
              WHERE id = :id
              LIMIT 1',
            ['id' => $id]
        );

        return $rows[0] ?? null;
    }

    public static function updateLastLogin(int $id): void
    {
        Database::execute(
            'UPDATE usuarios_admin SET ultimo_acceso_en = NOW() WHERE id = :id',
            ['id' => $id]
        );
    }

    public static function countAll(): int
    {
        $rows = Database::fetchAll('SELECT COUNT(*) AS total FROM usuarios_admin');
        return (int) ($rows[0]['total'] ?? 0);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function getAll(): array
    {
        return Database::fetchAll(
            'SELECT id, usuario AS username, correo AS email, rol AS role,
                    activo AS is_active, ultimo_acceso_en AS last_login_at, creado_en AS created_at
               FROM usuarios_admin
           ORDER BY id ASC'
        );
    }

    public static function existsConflict(string $username, string $email, ?int $excludeId = null): ?string
    {
        if ($excludeId !== null) {
            $rows = Database::fetchAll(
                'SELECT usuario, correo FROM usuarios_admin WHERE (usuario = :u OR correo = :e) AND id <> :id LIMIT 1',
                ['u' => $username, 'e' => $email, 'id' => $excludeId]
            );
        } else {
            $rows = Database::fetchAll(
                'SELECT usuario, correo FROM usuarios_admin WHERE usuario = :u OR correo = :e LIMIT 1',
                ['u' => $username, 'e' => $email]
            );
        }

        if ($rows === []) {
            return null;
        }

        if (strcasecmp((string) $rows[0]['usuario'], $username) === 0) {
            return 'El nombre de usuario ya está en uso por otra cuenta.';
        }
        return 'El correo electrónico ya está registrado en otra cuenta administrativa.';
    }

    public static function countActiveAdmins(?int $excludeId = null): int
    {
        if ($excludeId !== null) {
            $rows = Database::fetchAll(
                'SELECT COUNT(*) AS total FROM usuarios_admin WHERE rol = :r AND activo = 1 AND id <> :id',
                ['r' => 'admin', 'id' => $excludeId]
            );
        } else {
            $rows = Database::fetchAll(
                'SELECT COUNT(*) AS total FROM usuarios_admin WHERE rol = :r AND activo = 1',
                ['r' => 'admin']
            );
        }
        return (int) ($rows[0]['total'] ?? 0);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function save(?int $id, array $data): int
    {
        $password = (string) ($data['password'] ?? '');

        if ($id !== null && $id > 0) {
            if ($password !== '') {
                Database::execute(
                    'UPDATE usuarios_admin
                        SET usuario = :u, correo = :e, clave_hash = :h, rol = :r, activo = :a
                      WHERE id = :id',
                    [
                        'u'  => $data['username'],
                        'e'  => $data['email'],
                        'h'  => password_hash($password, PASSWORD_BCRYPT),
                        'r'  => $data['role'],
                        'a'  => $data['is_active'] ? 1 : 0,
                        'id' => $id,
                    ]
                );
            } else {
                Database::execute(
                    'UPDATE usuarios_admin
                        SET usuario = :u, correo = :e, rol = :r, activo = :a
                      WHERE id = :id',
                    [
                        'u'  => $data['username'],
                        'e'  => $data['email'],
                        'r'  => $data['role'],
                        'a'  => $data['is_active'] ? 1 : 0,
                        'id' => $id,
                    ]
                );
            }
            return $id;
        }

        Database::execute(
            'INSERT INTO usuarios_admin (usuario, correo, clave_hash, rol, activo)
             VALUES (:u, :e, :h, :r, :a)',
            [
                'u' => $data['username'],
                'e' => $data['email'],
                'h' => password_hash($password, PASSWORD_BCRYPT),
                'r' => $data['role'],
                'a' => $data['is_active'] ? 1 : 0,
            ]
        );
        return (int) Database::getConnection()->lastInsertId();
    }

    public static function delete(int $id): void
    {
        Database::execute('DELETE FROM usuarios_admin WHERE id = :id', ['id' => $id]);
    }
}
