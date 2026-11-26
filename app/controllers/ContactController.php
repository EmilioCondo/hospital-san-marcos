<?php
declare(strict_types=1);

/**
 * Controlador de recepción POST para el formulario de Contacto (/contacto)
 */

if (!defined('HSM_APP')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

class ContactController
{
    public const FLASH_KEY = 'contact_confirmed';

    /**
     * @return array{errors: array<string, string>, old: array<string, string>}
     */
    public static function handlePost(): array
    {
        Security::ensureSession();

        // 1. Honeypot anti-bots
        $honeypot = trim((string) ($_POST['website_url'] ?? ''));
        if ($honeypot !== '') {
            header('Location: ' . url('/contacto'), true, 303);
            exit;
        }

        // 2. Verificar CSRF
        if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            $check = ContactValidator::validate($_POST);
            http_response_code(422);
            return [
                'errors' => ['general' => 'Tu sesión expiró por seguridad. Por favor vuelve a enviar tu mensaje.'],
                'old'    => $check['old'],
            ];
        }

        // 3. Límite de envíos por IP
        if (!Security::checkRateLimit('contacto', 6, 15)) {
            $check = ContactValidator::validate($_POST);
            http_response_code(422);
            return [
                'errors' => ['general' => 'Has enviado varios mensajes recientemente. Espera unos minutos o comunícate al ' . HOSPITAL_PHONE_DISPLAY . '.'],
                'old'    => $check['old'],
            ];
        }

        // 4. Validar campos
        $validation = ContactValidator::validate($_POST);
        if (!$validation['valid']) {
            http_response_code(422);
            return [
                'errors' => $validation['errors'],
                'old'    => $validation['old'],
            ];
        }

        // 5. Persistir mensaje en MySQL
        $clean = $validation['clean'];
        $clean['ip_hash'] = Security::ipHash();

        try {
            $created = ContactMessageRepository::create($clean);
        } catch (DataSourceException $e) {
            http_response_code(503);
            return [
                'errors' => ['general' => 'No pudimos registrar tu mensaje en este momento. Intenta nuevamente en unos minutos o llámanos al ' . HOSPITAL_PHONE_DISPLAY . '.'],
                'old'    => $validation['old'],
            ];
        }

        Security::recordAttempt('contacto');

        // 6. Enviar notificaciones por correo
        $mailOk = Mailer::sendContactNotifications($created['code'], $clean);
        ContactMessageRepository::updateEmailStatus($created['id'], $mailOk ? 'enviado' : 'error');

        // 7. Flash PRG 303
        Security::setFlash(self::FLASH_KEY, [
            'code'          => $created['code'],
            'name'          => $clean['name'],
            'email'         => $clean['email'],
            'subject_label' => $clean['subject_label'],
            'email_sent'    => $mailOk,
        ]);

        header('Location: ' . url('/contacto') . '?enviado=1#contacto-confirmacion', true, 303);
        exit;
    }
}
