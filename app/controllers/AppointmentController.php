<?php
declare(strict_types=1);

/**
 * Controlador de recepción POST para el formulario de Agendamiento de Citas (/agendar-cita)
 */

if (!defined('HSM_APP')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

class AppointmentController
{
    public const FLASH_KEY = 'appointment_confirmed';

    /**
     * Procesa el envío POST de /agendar-cita.
     * En caso de éxito realiza redirección 303 (PRG) con los datos de confirmación en sesión.
     * En caso de error devuelve el estado con HTTP 422 para renderizar en la vista.
     *
     * @return array{errors: array<string, string>, errorStep: int, old: array<string, string>}
     */
    public static function handlePost(): array
    {
        Security::ensureSession();

        // 1. Verificar honeypot anti-bots (campo oculto que debe llegar vacío)
        $honeypot = trim((string) ($_POST['website_url'] ?? ''));
        if ($honeypot !== '') {
            header('Location: ' . url('/agendar-cita'), true, 303);
            exit;
        }

        // 2. Verificar token CSRF
        if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            $check = AppointmentValidator::validate($_POST);
            http_response_code(422);
            return [
                'errors'    => ['general' => 'Tu sesión expiró por seguridad. Por favor revisa el resumen y vuelve a confirmar tu solicitud.'],
                'errorStep' => 4,
                'old'       => $check['old'],
            ];
        }

        // 3. Verificar límite de solicitudes por IP
        if (!Security::checkRateLimit('agendar-cita', 8, 15)) {
            $check = AppointmentValidator::validate($_POST);
            http_response_code(422);
            return [
                'errors'    => ['general' => 'Has enviado demasiadas solicitudes en pocos minutos. Espera unos minutos o comunícate al ' . HOSPITAL_PHONE_DISPLAY . '.'],
                'errorStep' => 4,
                'old'       => $check['old'],
            ];
        }

        // 4. Validar campos de los 4 pasos
        $validation = AppointmentValidator::validate($_POST);
        if (!$validation['valid']) {
            http_response_code(422);
            return [
                'errors'    => $validation['errors'],
                'errorStep' => $validation['errorStep'],
                'old'       => $validation['old'],
            ];
        }

        // 5. Persistir la cita en MySQL
        $clean = $validation['clean'];
        $clean['ip_hash'] = Security::ipHash();

        try {
            $created = AppointmentRepository::create($clean);
        } catch (DataSourceException $e) {
            http_response_code(503);
            return [
                'errors'    => ['general' => 'No pudimos registrar tu solicitud en este momento. Por favor intenta nuevamente en unos minutos o llámanos al ' . HOSPITAL_PHONE_DISPLAY . '.'],
                'errorStep' => 4,
                'old'       => $validation['old'],
            ];
        }

        Security::recordAttempt('agendar-cita');

        // 6. Enviar notificaciones por correo (si falla SMTP, la cita permanece guardada)
        $mailOk = Mailer::sendAppointmentNotifications($created['code'], $clean);
        AppointmentRepository::updateEmailStatus($created['id'], $mailOk ? 'enviado' : 'error');

        // 7. Guardar resumen en flash de sesión y redirigir (PRG 303)
        Security::setFlash(self::FLASH_KEY, [
            'code'             => $created['code'],
            'attention_label'  => $clean['attention_label'],
            'doctor_label'     => $clean['doctor_label'],
            'appointment_date' => $clean['appointment_date'],
            'shift_label'      => $clean['shift_label'],
            'patient_name'     => $clean['patient_name'],
            'patient_doc_type' => $clean['patient_doc_type'],
            'patient_id_doc'   => $clean['patient_id_doc'],
            'patient_phone'    => $clean['patient_phone'],
            'patient_email'    => $clean['patient_email'],
            'reason'           => $clean['reason'],
            'email_sent'       => $mailOk && !empty($clean['patient_email']),
        ]);

        header('Location: ' . url('/agendar-cita') . '?confirmada=1#cita-confirmacion', true, 303);
        exit;
    }
}
