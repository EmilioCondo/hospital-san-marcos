<?php
declare(strict_types=1);

/**
 * Servicio de autenticación, control de sesión e inactividad y autorización por rol (/admin)
 */

if (!defined('HSM_APP')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

class AdminAuth
{
    private const SESSION_KEY    = '_hsm_admin';
    private const IDLE_TIMEOUT_S = 1800; // 30 minutos de inactividad máxima
    private const DUMMY_BCRYPT   = '$2y$10$usesomesillystringfore7hnbRJHxXVLeakoG8K30oukPsA.ztMG';

    public static function sendNoCacheHeaders(): void
    {
        if (!headers_sent()) {
            header('X-Robots-Tag: noindex, nofollow');
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Pragma: no-cache');
        }
    }

    /**
     * Obtiene el usuario autenticado actual o null si no inició sesión o expiró por inactividad.
     *
     * @return array{id: int, username: string, email: string, role: string}|null
     */
    public static function user(): ?array
    {
        Security::ensureSession();

        if (!isset($_SESSION[self::SESSION_KEY]) || !is_array($_SESSION[self::SESSION_KEY])) {
            return null;
        }

        $data = $_SESSION[self::SESSION_KEY];
        $lastActivity = (int) ($data['last_activity'] ?? 0);

        if ($lastActivity <= 0 || (time() - $lastActivity) > self::IDLE_TIMEOUT_S) {
            unset($_SESSION[self::SESSION_KEY]);
            Security::setFlash('admin_auth_notice', [
                'type'    => 'info',
                'message' => 'Tu sesión administrativa se cerró por inactividad. Inicia sesión nuevamente.',
            ]);
            return null;
        }

        // Verificar que la cuenta continúe activa en base de datos
        $fresh = AdminUserRepository::findById((int) ($data['id'] ?? 0));
        if ($fresh === null || !(bool) $fresh['is_active']) {
            unset($_SESSION[self::SESSION_KEY]);
            return null;
        }

        $_SESSION[self::SESSION_KEY]['last_activity'] = time();
        $_SESSION[self::SESSION_KEY]['role']          = (string) $fresh['role'];
        $_SESSION[self::SESSION_KEY]['username']      = (string) $fresh['username'];
        $_SESSION[self::SESSION_KEY]['email']         = (string) $fresh['email'];

        return [
            'id'       => (int) $fresh['id'],
            'username' => (string) $fresh['username'],
            'email'    => (string) $fresh['email'],
            'role'     => (string) $fresh['role'],
        ];
    }

    /**
     * Exige sesión administrativa activa; de lo contrario redirige a /admin/login.
     *
     * @return array{id: int, username: string, email: string, role: string}
     */
    public static function requireAuth(): array
    {
        self::sendNoCacheHeaders();
        $user = self::user();
        if ($user === null) {
            header('Location: ' . url('/admin/login'), true, 302);
            exit;
        }
        return $user;
    }

    /**
     * @param array<int, string> $allowedRoles
     */
    public static function hasRole(array $user, array $allowedRoles): bool
    {
        return in_array($user['role'], $allowedRoles, true);
    }

    /**
     * Verifica credenciales con protección contra fuerza bruta y enumeración por tiempos.
     *
     * @return array{ok: bool, error: string|null}
     */
    public static function attemptLogin(string $identifier, string $password): array
    {
        Security::ensureSession();

        if (!Security::checkRateLimit('admin-login', 5, 15)) {
            return [
                'ok'    => false,
                'error' => 'Demasiados intentos fallidos. Por seguridad, espera 15 minutos antes de volver a intentarlo.',
            ];
        }

        $cleanId = trim($identifier);
        if ($cleanId === '' || $password === '') {
            return [
                'ok'    => false,
                'error' => 'Ingresa tu usuario (o correo) y contraseña.',
            ];
        }

        $record = AdminUserRepository::findByUsernameOrEmail($cleanId);
        $hashToVerify = $record !== null ? (string) $record['password_hash'] : self::DUMMY_BCRYPT;
        $validPass = password_verify($password, $hashToVerify);

        if ($record === null || !$validPass || !(bool) $record['is_active']) {
            Security::recordAttempt('admin-login');
            return [
                'ok'    => false,
                'error' => 'Credenciales inválidas o cuenta inactiva.',
            ];
        }

        session_regenerate_id(true);
        // Renovar también el token CSRF tras autenticar
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));

        $_SESSION[self::SESSION_KEY] = [
            'id'            => (int) $record['id'],
            'username'      => (string) $record['username'],
            'email'         => (string) $record['email'],
            'role'          => (string) $record['role'],
            'last_activity' => time(),
        ];

        AdminUserRepository::updateLastLogin((int) $record['id']);

        return [
            'ok'    => true,
            'error' => null,
        ];
    }

    public static function logout(): void
    {
        Security::ensureSession();
        unset($_SESSION[self::SESSION_KEY]);
        session_regenerate_id(true);
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }

    public static function roleLabel(string $role): string
    {
        return match ($role) {
            'admin'     => 'Administrador General',
            'editor'    => 'Editor Institucional',
            'recepcion' => 'Admisión y Recepción',
            default     => ucfirst($role),
        };
    }
}
