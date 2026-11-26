<?php
declare(strict_types=1);

/**
 * Configuración global de la aplicación Hospital San Marcos
 * Entorno: XAMPP (PHP 8.2) — Etapa 1: Portal Web Institucional Informativo
 */

// Prevenir acceso directo si no fue invocado desde el controlador frontal
if (!defined('HSM_APP')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

// Zona horaria oficial de Ecuador continental (Pasaje, El Oro)
date_default_timezone_set('America/Guayaquil');

// Detección dinámica o fija de la URL base en XAMPP
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$baseUrl = rtrim($scriptDir, '/');
define('BASE_URL', $baseUrl !== '' ? $baseUrl : '/hospital-san-marcos');

// Información institucional global
define('HOSPITAL_NAME', 'Hospital San Marcos');
define('HOSPITAL_LOCATION', 'Pasaje, El Oro, Ecuador');
define('HOSPITAL_ADDRESS', 'Av. Quito y Sucre, Pasaje, El Oro, Ecuador');
define('HOSPITAL_PHONE_DISPLAY', '098 797 3511');
define('HOSPITAL_PHONE_LINK', 'tel:0987973511');
define('HOSPITAL_WHATSAPP_URL', 'https://wa.me/593987973511');
define('HOSPITAL_INSTAGRAM_URL', 'https://instagram.com/hospital_sanmarcosec/');
define('HOSPITAL_FACEBOOK_URL', 'https://facebook.com/hospitalsanmarcospasaje/');
define('HOSPITAL_WEBSITE_URL', 'https://hospitalsanmarcos.com.ec/');

// Conexión MySQL/MariaDB (valores por defecto de XAMPP)
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'hospital_san_marcos');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

require_once __DIR__ . '/database.php';

if (is_file(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

/**
 * Función auxiliar para generar URLs internas respetando BASE_URL
 */
function url(string $path = ''): string
{
    $cleanPath = ltrim($path, '/');
    if ($cleanPath === '') {
        return BASE_URL . '/';
    }
    return BASE_URL . '/' . $cleanPath;
}

/**
 * Función auxiliar para generar URLs de recursos estáticos en /public
 */
function asset(string $path): string
{
    return BASE_URL . '/public/' . ltrim($path, '/');
}

/**
 * Función auxiliar para escapar texto HTML de forma segura
 */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Valida y limpia parámetros de consulta URL (?search=, ?doctor=)
 */
function sanitize_query_param(mixed $value, int $maxLength = 80): string
{
    if (!is_string($value)) {
        return '';
    }
    $clean = strip_tags($value);
    $clean = preg_replace('/[\x00-\x1F\x7F]/u', '', $clean) ?? '';
    $clean = trim($clean);
    if (mb_strlen($clean, 'UTF-8') > $maxLength) {
        $clean = mb_substr($clean, 0, $maxLength, 'UTF-8');
    }
    return $clean;
}
