<?php
declare(strict_types=1);

/**
 * Script CLI local para crear o actualizar un usuario administrativo en MySQL (tabla usuarios_admin).
 * Uso interactivo:
 *   C:\xampp\php\php.exe C:\xampp\htdocs\hospital-san-marcos\database\create_admin.php
 *
 * Uso por tubería:
 *   php database/create_admin.php --username=admin --email=admin@hospitalsanmarcos.com.ec --role=admin --password-stdin
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Acceso permitido únicamente desde consola CLI.');
}

define('HSM_APP', true);
require_once dirname(__DIR__) . '/config/config.php';

$opts = getopt('', ['username::', 'email::', 'role::', 'password-stdin', 'update']);

function promptLine(string $label, string $default = ''): string
{
    $suffix = $default !== '' ? " [{$default}]" : '';
    fwrite(STDOUT, "{$label}{$suffix}: ");
    $line = fgets(STDIN);
    if ($line === false) {
        return $default;
    }
    $val = trim($line);
    return $val !== '' ? $val : $default;
}

fwrite(STDOUT, "=== Hospital San Marcos — Gestión CLI de Usuario Administrativo ===\n\n");

$username = isset($opts['username']) && is_string($opts['username'])
    ? trim($opts['username'])
    : promptLine('Nombre de usuario (ej. admin)');

$email = isset($opts['email']) && is_string($opts['email'])
    ? strtolower(trim($opts['email']))
    : strtolower(promptLine('Correo electrónico institucional'));

$role = isset($opts['role']) && is_string($opts['role'])
    ? strtolower(trim($opts['role']))
    : strtolower(promptLine('Rol (admin / editor / recepcion)', 'admin'));

if (isset($opts['password-stdin'])) {
    $rawPass = fgets(STDIN);
    $password = $rawPass === false ? '' : rtrim($rawPass, "\r\n");
} else {
    $password = promptLine('Contraseña (mínimo 8 caracteres)');
}

$errors = [];
if (preg_match('/^[a-zA-Z0-9._\-]{3,60}$/', $username) !== 1) {
    $errors[] = 'El nombre de usuario debe tener entre 3 y 60 caracteres (letras, números, punto, guion o guion bajo).';
}
if ($email === '' || mb_strlen($email, 'UTF-8') > 150 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
    $errors[] = 'Ingresa un correo electrónico válido.';
}
if (!in_array($role, ['admin', 'editor', 'recepcion'], true)) {
    $errors[] = 'El rol debe ser admin, editor o recepcion.';
}
if (mb_strlen($password, 'UTF-8') < 8) {
    $errors[] = 'La contraseña debe tener al menos 8 caracteres.';
}

if ($errors !== []) {
    fwrite(STDERR, "\nErrores de validación:\n");
    foreach ($errors as $err) {
        fwrite(STDERR, "  - {$err}\n");
    }
    exit(1);
}

$hash = password_hash($password, PASSWORD_BCRYPT);

$existing = Database::fetchAll(
    'SELECT id, usuario FROM usuarios_admin WHERE usuario = :u OR correo = :e LIMIT 1',
    ['u' => $username, 'e' => $email]
);

if ($existing !== []) {
    if (!isset($opts['update'])) {
        fwrite(
            STDERR,
            "\nYa existe un usuario con ese nombre o correo (ID #{$existing[0]['id']}: {$existing[0]['usuario']}).\n"
            . "Agrega la bandera --update si deseas actualizar su contraseña y rol.\n"
        );
        exit(1);
    }

    Database::execute(
        'UPDATE usuarios_admin
            SET usuario = :u, correo = :e, clave_hash = :h, rol = :r, activo = 1
          WHERE id = :id',
        [
            'u'  => $username,
            'e'  => $email,
            'h'  => $hash,
            'r'  => $role,
            'id' => (int) $existing[0]['id'],
        ]
    );

    fwrite(STDOUT, "\n[OK] Usuario '{$username}' (rol: {$role}) actualizado correctamente.\n");
    exit(0);
}

Database::execute(
    'INSERT INTO usuarios_admin (usuario, correo, clave_hash, rol, activo)
     VALUES (:u, :e, :h, :r, 1)',
    [
        'u' => $username,
        'e' => $email,
        'h' => $hash,
        'r' => $role,
    ]
);

fwrite(STDOUT, "\n[OK] Usuario administrativo '{$username}' (rol: {$role}) creado exitosamente.\n");
exit(0);
