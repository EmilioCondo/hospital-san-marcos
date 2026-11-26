<?php
declare(strict_types=1);

/**
 * Plantilla de configuración de correo electrónico (PHPMailer)
 * Copia este archivo como config/mail.php y ajusta las credenciales cuando conectes SMTP real.
 */

if (!defined('HSM_APP')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

return [
    // 'eml'  => Modo desarrollo: guarda cada correo como archivo RFC-822 (.eml) en storage/mail/
    // 'smtp' => Producción: envía por servidor SMTP autenticado con PHPMailer
    'driver'          => 'eml',

    // Configuración SMTP (utilizada cuando 'driver' => 'smtp')
    'smtp_host'       => 'smtp.gmail.com',
    'smtp_port'       => 587,
    'smtp_encryption' => 'tls', // 'tls' (STARTTLS puerto 587) o 'ssl' (SMTPS puerto 465)
    'smtp_auth'       => true,
    'smtp_username'   => '',
    'smtp_password'   => '',
    'smtp_timeout'    => 12,

    // Remitente y destinatarios institucionales
    'from_email'      => 'notificaciones@hospitalsanmarcos.com.ec',
    'from_name'       => 'Hospital San Marcos - Pasaje',
    'admissions_to'   => 'admision@hospitalsanmarcos.com.ec',
    'contact_to'      => 'contacto@hospitalsanmarcos.com.ec',

    // Carpeta local protegida para modo desarrollo (.eml)
    'eml_dir'         => dirname(__DIR__) . '/storage/mail',
];
