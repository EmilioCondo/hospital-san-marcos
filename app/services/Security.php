<?php
declare(strict_types=1);

/**
 * Servicios de seguridad de formularios: sesiones seguras, CSRF, mensajes flash (PRG),
 * anonimización de IP (HMAC-SHA256) y límite de envíos por ventana de tiempo.
 */

if (!defined('HSM_APP')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

class Security
{
    private const CODE_ALPHABET = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';

    public static function ensureSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        if (!headers_sent()) {
            ini_set('session.use_strict_mode', '1');
            ini_set('session.use_only_cookies', '1');
            session_set_cookie_params([
                'lifetime' => 0,
                'path'     => (defined('BASE_URL') && BASE_URL !== '') ? BASE_URL . '/' : '/',
                'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }

        session_name('HSM_SESSID');
        session_start();
    }

    public static function csrfToken(): string
    {
        self::ensureSession();
        if (empty($_SESSION['_csrf_token']) || !is_string($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }

    public static function verifyCsrf(mixed $token): bool
    {
        self::ensureSession();
        if (!is_string($token) || $token === '' || empty($_SESSION['_csrf_token'])) {
            return false;
        }
        return hash_equals((string) $_SESSION['_csrf_token'], $token);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function setFlash(string $key, array $payload): void
    {
        self::ensureSession();
        $_SESSION['_hsm_flash'][$key] = $payload;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function pullFlash(string $key): ?array
    {
        self::ensureSession();
        if (!isset($_SESSION['_hsm_flash'][$key]) || !is_array($_SESSION['_hsm_flash'][$key])) {
            return null;
        }
        $data = $_SESSION['_hsm_flash'][$key];
        unset($_SESSION['_hsm_flash'][$key]);
        return $data;
    }

    /**
     * Calcula un hash irreversible de la IP del cliente sin almacenar la IP en claro.
     */
    public static function ipHash(): string
    {
        $rawIp = (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
        return hash_hmac('sha256', $rawIp, self::secretKey());
    }

    /**
     * Verifica si la IP actual puede realizar un envío en la ventana configurada.
     */
    public static function checkRateLimit(string $formKey, int $maxAttempts = 8, int $windowMinutes = 15): bool
    {
        try {
            // Limpieza ocasional (1 de cada 20 peticiones) de registros mayores a 24h
            if (random_int(1, 20) === 1) {
                Database::execute('DELETE FROM limites_envio WHERE creado_en < (NOW() - INTERVAL 24 HOUR)');
            }

            $rows = Database::fetchAll(
                'SELECT COUNT(*) AS total
                   FROM limites_envio
                  WHERE formulario = :form_key
                    AND hash_ip = :ip_hash
                    AND creado_en >= (NOW() - INTERVAL :mins MINUTE)',
                [
                    'form_key' => $formKey,
                    'ip_hash'  => self::ipHash(),
                    'mins'     => $windowMinutes,
                ]
            );

            $count = (int) ($rows[0]['total'] ?? 0);
            return $count < $maxAttempts;
        } catch (Throwable $e) {
            error_log('[HSM][RATE] ' . $e->getMessage());
            return true;
        }
    }

    public static function recordAttempt(string $formKey): void
    {
        try {
            Database::execute(
                'INSERT INTO limites_envio (formulario, hash_ip, creado_en) VALUES (:form_key, :ip_hash, NOW())',
                [
                    'form_key' => $formKey,
                    'ip_hash'  => self::ipHash(),
                ]
            );
        } catch (Throwable $e) {
            error_log('[HSM][RATE] ' . $e->getMessage());
        }
    }

    /**
     * Genera un código alfanumérico sin caracteres ambiguos (ej. HCSM-XXXX o CONT-XXXX).
     */
    public static function generateCode(string $prefix = 'HCSM', int $length = 4): string
    {
        $maxIdx = strlen(self::CODE_ALPHABET) - 1;
        $suffix = '';
        for ($i = 0; $i < $length; $i++) {
            $suffix .= self::CODE_ALPHABET[random_int(0, $maxIdx)];
        }
        return $prefix . '-' . $suffix;
    }

    /**
     * Convierte un texto en español a un identificador URL amigable (slug).
     */
    public static function slugify(string $text): string
    {
        $map = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
            'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u', 'Ü' => 'u', 'Ñ' => 'n',
        ];
        $lower = mb_strtolower(strtr(trim($text), $map), 'UTF-8');
        $slug  = preg_replace('/[^a-z0-9]+/', '-', $lower) ?? '';
        $slug  = trim($slug, '-');
        return $slug !== '' ? substr($slug, 0, 95) : ('item-' . strtolower(self::generateCode('ID', 4)));
    }

    private static function secretKey(): string
    {
        $secretPath = dirname(__DIR__, 2) . '/storage/.app_secret';
        if (is_file($secretPath)) {
            $existing = trim((string) @file_get_contents($secretPath));
            if (strlen($existing) >= 32) {
                return $existing;
            }
        }

        $generated = bin2hex(random_bytes(32));
        @file_put_contents($secretPath, $generated, LOCK_EX);
        return $generated;
    }
}
