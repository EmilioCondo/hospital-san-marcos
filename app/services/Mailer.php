<?php
declare(strict_types=1);

/**
 * Servicio centralizado de correo electrónico con PHPMailer:
 * - Modo 'eml' (desarrollo): genera archivos RFC-822 completos en storage/mail/*.eml
 * - Modo 'smtp' (producción): envía mediante servidor SMTP autenticado
 */

if (!defined('HSM_APP')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

use PHPMailer\PHPMailer\PHPMailer;

class Mailer
{
    /** @var array<string, mixed>|null */
    private static ?array $config = null;

    /**
     * Envía las notificaciones de una solicitud de cita médica (a Admisión y copia al paciente si indicó correo).
     *
     * @param array<string, mixed> $clean
     */
    public static function sendAppointmentNotifications(string $code, array $clean): bool
    {
        $cfg = self::getConfig();

        $docTypeLabel = ($clean['patient_doc_type'] ?? 'cedula') === 'pasaporte' ? 'Pasaporte' : 'Cédula';
        $emailDisplay = !empty($clean['patient_email']) ? (string) $clean['patient_email'] : 'No proporcionado';
        $reasonDisplay = !empty($clean['reason']) ? (string) $clean['reason'] : 'Sin comentarios adicionales';

        // 1. Correo interno para Admisión
        $adminSubject = sprintf('[Cita %s] Nueva solicitud — %s', $code, (string) $clean['attention_label']);
        $adminHtml = self::wrapHtml(
            'Nueva solicitud de cita médica en línea',
            sprintf('Código de solicitud: <strong>%s</strong> (Estado: Pendiente de confirmación)', e($code)),
            [
                'Código de registro'      => $code,
                'Especialidad / Servicio' => (string) $clean['attention_label'],
                'Médico solicitado'       => (string) $clean['doctor_label'],
                'Fecha preferida'         => (string) $clean['appointment_date'],
                'Franja horaria'          => (string) $clean['shift_label'],
                'Paciente'                => (string) $clean['patient_name'],
                $docTypeLabel             => (string) $clean['patient_id_doc'],
                'Teléfono / WhatsApp'     => (string) $clean['patient_phone'],
                'Correo electrónico'      => $emailDisplay,
                'Motivo / Comentarios'    => $reasonDisplay,
            ],
            'Por favor, contacte al paciente para validar la agenda del especialista y confirmar el horario oficial.'
        );
        $adminText = self::buildPlainText(
            "NUEVA SOLICITUD DE CITA MEDICA — {$code}",
            [
                'Codigo'              => $code,
                'Atencion'            => (string) $clean['attention_label'],
                'Medico'              => (string) $clean['doctor_label'],
                'Fecha preferida'     => (string) $clean['appointment_date'],
                'Turno'               => (string) $clean['shift_label'],
                'Paciente'            => (string) $clean['patient_name'],
                $docTypeLabel         => (string) $clean['patient_id_doc'],
                'Telefono'            => (string) $clean['patient_phone'],
                'Correo'              => $emailDisplay,
                'Motivo/Comentarios'  => $reasonDisplay,
            ]
        );

        $replyTo = !empty($clean['patient_email']) ? (string) $clean['patient_email'] : null;
        $okAdmin = self::dispatch(
            (string) $cfg['admissions_to'],
            'Admisión Hospital San Marcos',
            $adminSubject,
            $adminHtml,
            $adminText,
            $replyTo,
            (string) $clean['patient_name'],
            $code . '_admision'
        );

        // 2. Copia al paciente (únicamente si ingresó correo electrónico)
        $okPatient = true;
        if (!empty($clean['patient_email'])) {
            $patientSubject = sprintf('Solicitud de cita registrada (%s) — Hospital San Marcos', $code);
            $patientHtml = self::wrapHtml(
                'Hemos registrado tu solicitud de cita',
                sprintf('Hola <strong>%s</strong>, recibimos tu solicitud con el código <strong>%s</strong>.', e((string) $clean['patient_name']), e($code)),
                [
                    'Código de seguimiento'   => $code,
                    'Estado actual'           => 'Solicitud registrada — Pendiente de confirmación por Admisión',
                    'Especialidad / Servicio' => (string) $clean['attention_label'],
                    'Médico preferido'        => (string) $clean['doctor_label'],
                    'Fecha solicitada'        => (string) $clean['appointment_date'],
                    'Franja horaria'          => (string) $clean['shift_label'],
                    'Teléfono de contacto'    => (string) $clean['patient_phone'],
                    'Sede de atención'        => HOSPITAL_ADDRESS,
                ],
                'Nuestro equipo de Admisión verificará la agenda y se comunicará contigo por teléfono o WhatsApp para confirmar tu horario definitivo. Ante cualquier urgencia médica comunícate las 24 horas al ' . HOSPITAL_PHONE_DISPLAY . '.'
            );
            $patientText = self::buildPlainText(
                "SOLICITUD DE CITA REGISTRADA ({$code}) — HOSPITAL SAN MARCOS",
                [
                    'Codigo'          => $code,
                    'Estado'          => 'Pendiente de confirmacion por Admision',
                    'Especialidad'    => (string) $clean['attention_label'],
                    'Medico'          => (string) $clean['doctor_label'],
                    'Fecha preferida' => (string) $clean['appointment_date'],
                    'Turno'           => (string) $clean['shift_label'],
                    'Direccion'       => HOSPITAL_ADDRESS,
                    'Telefono 24/7'   => HOSPITAL_PHONE_DISPLAY,
                ]
            );

            $okPatient = self::dispatch(
                (string) $clean['patient_email'],
                (string) $clean['patient_name'],
                $patientSubject,
                $patientHtml,
                $patientText,
                null,
                '',
                $code . '_paciente'
            );
        }

        return $okAdmin && $okPatient;
    }

    /**
     * Envía las notificaciones de un mensaje de contacto institucional (al Hospital y acuse al remitente).
     *
     * @param array<string, mixed> $clean
     */
    public static function sendContactNotifications(string $code, array $clean): bool
    {
        $cfg = self::getConfig();
        $phoneDisplay = !empty($clean['phone']) ? (string) $clean['phone'] : 'No indicado';

        // 1. Correo interno para Atención al Paciente / Administración
        $adminSubject = sprintf('[Contacto %s] %s', $code, (string) $clean['subject_label']);
        $adminHtml = self::wrapHtml(
            'Nuevo mensaje desde el portal web institucional',
            sprintf('Referencia de contacto: <strong>%s</strong>', e($code)),
            [
                'Código'             => $code,
                'Remitente'          => (string) $clean['name'],
                'Correo electrónico' => (string) $clean['email'],
                'Teléfono'           => $phoneDisplay,
                'Asunto'             => (string) $clean['subject_label'],
                'Mensaje'            => (string) $clean['message'],
            ],
            'Puedes responder directamente a este correo para comunicarte con el remitente.'
        );
        $adminText = self::buildPlainText(
            "NUEVO MENSAJE DE CONTACTO — {$code}",
            [
                'Codigo'    => $code,
                'Remitente' => (string) $clean['name'],
                'Correo'    => (string) $clean['email'],
                'Telefono'  => $phoneDisplay,
                'Asunto'    => (string) $clean['subject_label'],
                'Mensaje'   => (string) $clean['message'],
            ]
        );

        $okAdmin = self::dispatch(
            (string) $cfg['contact_to'],
            'Atención al Paciente — Hospital San Marcos',
            $adminSubject,
            $adminHtml,
            $adminText,
            (string) $clean['email'],
            (string) $clean['name'],
            $code . '_hospital'
        );

        // 2. Acuse de recibo al ciudadano
        $userSubject = sprintf('Hemos recibido tu mensaje (%s) — Hospital San Marcos', $code);
        $userHtml = self::wrapHtml(
            'Acuse de recepción de consulta',
            sprintf('Hola <strong>%s</strong>, hemos recibido tu mensaje con referencia <strong>%s</strong>.', e((string) $clean['name']), e($code)),
            [
                'Referencia' => $code,
                'Asunto'     => (string) $clean['subject_label'],
                'Teléfono'   => $phoneDisplay,
                'Mensaje'    => (string) $clean['message'],
            ],
            'Nuestro equipo administrativo revisará tu requerimiento y te responderá a la brevedad posible. Para emergencias médicas las 24 horas comunícate al ' . HOSPITAL_PHONE_DISPLAY . '.'
        );
        $userText = self::buildPlainText(
            "ACUSE DE RECIBO ({$code}) — HOSPITAL SAN MARCOS",
            [
                'Referencia'    => $code,
                'Asunto'        => (string) $clean['subject_label'],
                'Direccion'     => HOSPITAL_ADDRESS,
                'Emergencias'   => HOSPITAL_PHONE_DISPLAY,
            ]
        );

        $okUser = self::dispatch(
            (string) $clean['email'],
            (string) $clean['name'],
            $userSubject,
            $userHtml,
            $userText,
            null,
            '',
            $code . '_remitente'
        );

        return $okAdmin && $okUser;
    }

    /**
     * Envía notificación automática al paciente cuando Admisión confirma (con hora asignada) o cancela su cita.
     *
     * @param array<string, mixed> $appointment
     */
    public static function sendAppointmentStatusUpdate(
        array $appointment,
        string $newStatus,
        ?string $confirmedTime,
        ?string $adminNotes
    ): bool {
        $patientEmail = trim((string) ($appointment['patient_email'] ?? ''));
        if ($patientEmail === '' || !in_array($newStatus, ['confirmada', 'cancelada'], true)) {
            return false;
        }

        $code        = (string) $appointment['code'];
        $patientName = (string) $appointment['patient_name'];
        $shiftLabel  = ((string) ($appointment['shift'] ?? 'matutino')) === 'vespertino'
            ? 'Tarde (14:00 - 18:00)'
            : 'Mañana (08:00 - 12:00)';

        if ($newStatus === 'confirmada') {
            $timeDisplay = ($confirmedTime !== null && trim($confirmedTime) !== '')
                ? trim($confirmedTime)
                : $shiftLabel;

            $rows = [
                'Código de cita'          => $code,
                'Estado'                  => 'CONFIRMADA',
                'Especialidad / Servicio' => (string) $appointment['attention_label'],
                'Médico asignado'         => (string) $appointment['doctor_label'],
                'Fecha confirmada'        => (string) $appointment['appointment_date'],
                'Hora asignada'           => $timeDisplay,
                'Lugar de atención'       => HOSPITAL_ADDRESS,
            ];
            if ($adminNotes !== null && trim($adminNotes) !== '') {
                $rows['Indicaciones de Admisión'] = trim($adminNotes);
            }

            $subject = sprintf('Cita médica confirmada (%s) — Hospital San Marcos', $code);
            $html = self::wrapHtml(
                'Tu cita médica ha sido confirmada',
                sprintf(
                    'Hola <strong>%s</strong>, nuestro departamento de Admisión confirmó tu turno con código <strong>%s</strong>.',
                    e($patientName),
                    e($code)
                ),
                $rows,
                'Te recomendamos acudir 15 minutos antes de la hora asignada portando tu cédula o pasaporte. Si necesitas reprogramar, comunícate al ' . HOSPITAL_PHONE_DISPLAY . '.'
            );
            $text = self::buildPlainText("CITA MEDICA CONFIRMADA ({$code}) — HOSPITAL SAN MARCOS", $rows);

            return self::dispatch(
                $patientEmail,
                $patientName,
                $subject,
                $html,
                $text,
                null,
                '',
                $code . '_confirmada'
            );
        }

        // Estado: cancelada
        $rows = [
            'Código de solicitud'     => $code,
            'Estado'                  => 'CANCELADA',
            'Especialidad / Servicio' => (string) $appointment['attention_label'],
            'Fecha solicitada'        => (string) $appointment['appointment_date'],
        ];
        if ($adminNotes !== null && trim($adminNotes) !== '') {
            $rows['Observación de Admisión'] = trim($adminNotes);
        }

        $subject = sprintf('Actualización de tu solicitud de cita (%s) — Hospital San Marcos', $code);
        $html = self::wrapHtml(
            'Actualización sobre tu solicitud de cita',
            sprintf(
                'Hola <strong>%s</strong>, te informamos que la solicitud de cita con código <strong>%s</strong> ha sido marcada como cancelada.',
                e($patientName),
                e($code)
            ),
            $rows,
            'Si deseas elegir una nueva fecha o coordinar otro horario, puedes registrar una nueva solicitud en nuestro portal web o comunicarte directamente al ' . HOSPITAL_PHONE_DISPLAY . '.'
        );
        $text = self::buildPlainText("SOLICITUD DE CITA CANCELADA ({$code}) — HOSPITAL SAN MARCOS", $rows);

        return self::dispatch(
            $patientEmail,
            $patientName,
            $subject,
            $html,
            $text,
            null,
            '',
            $code . '_cancelada'
        );
    }

    private static function dispatch(
        string $toEmail,
        string $toName,
        string $subject,
        string $htmlBody,
        string $altBody,
        ?string $replyToEmail,
        string $replyToName,
        string $fileTag
    ): bool {
        if (!class_exists(PHPMailer::class)) {
            error_log('[HSM][MAIL] PHPMailer no está disponible en vendor/autoload.php.');
            return false;
        }

        $cfg = self::getConfig();

        try {
            $mail = new PHPMailer(true);
            $mail->CharSet = PHPMailer::CHARSET_UTF8;
            $mail->Encoding = PHPMailer::ENCODING_QUOTED_PRINTABLE;

            $mail->setFrom(
                self::sanitizeHeader((string) $cfg['from_email']),
                self::sanitizeHeader((string) $cfg['from_name'])
            );
            $mail->addAddress(
                self::sanitizeHeader($toEmail),
                self::sanitizeHeader($toName)
            );

            if ($replyToEmail !== null && $replyToEmail !== '') {
                $mail->addReplyTo(
                    self::sanitizeHeader($replyToEmail),
                    self::sanitizeHeader($replyToName)
                );
            }

            $mail->isHTML(true);
            $mail->Subject = self::sanitizeHeader($subject);
            $mail->Body    = $htmlBody;
            $mail->AltBody = $altBody;

            $driver = strtolower((string) ($cfg['driver'] ?? 'eml'));

            if ($driver === 'smtp') {
                $mail->isSMTP();
                $mail->Host       = (string) $cfg['smtp_host'];
                $mail->Port       = (int) $cfg['smtp_port'];
                $mail->SMTPAuth   = (bool) ($cfg['smtp_auth'] ?? true);
                $mail->Username   = (string) ($cfg['smtp_username'] ?? '');
                $mail->Password   = (string) ($cfg['smtp_password'] ?? '');
                $mail->Timeout    = (int) ($cfg['smtp_timeout'] ?? 12);

                $enc = strtolower((string) ($cfg['smtp_encryption'] ?? 'tls'));
                if ($enc === 'ssl') {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                } elseif ($enc === 'tls') {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                }

                return $mail->send();
            }

            // Modo desarrollo ('eml'): generar mensaje MIME completo sin conexión de red
            $mail->isSMTP();
            $mail->preSend();
            $rawMime = $mail->getSentMIMEMessage();

            $emlDir = (string) ($cfg['eml_dir'] ?? (dirname(__DIR__, 2) . '/storage/mail'));
            if (!is_dir($emlDir) && !@mkdir($emlDir, 0750, true) && !is_dir($emlDir)) {
                error_log('[HSM][MAIL] No fue posible crear el directorio storage/mail.');
                return false;
            }

            $safeTag  = preg_replace('/[^A-Za-z0-9_\-]/', '_', $fileTag) ?? 'correo';
            $fileName = sprintf('%s_%s.eml', date('Ymd_His'), $safeTag);
            $written  = @file_put_contents($emlDir . '/' . $fileName, $rawMime, LOCK_EX);

            return $written !== false;
        } catch (Throwable $e) {
            error_log(sprintf('[HSM][MAIL] Fallo en despacho (%s): %s', $fileTag, $e->getMessage()));
            return false;
        }
    }

    /**
     * @param array<string, string> $rows
     */
    private static function wrapHtml(string $heading, string $introHtml, array $rows, string $footerNote): string
    {
        $trs = '';
        foreach ($rows as $label => $val) {
            $trs .= sprintf(
                '<tr><td style="padding:8px 12px;border-bottom:1px solid #d9edf1;font-weight:600;color:#003e46;width:38%%;vertical-align:top;">%s</td><td style="padding:8px 12px;border-bottom:1px solid #d9edf1;color:#001f28;">%s</td></tr>',
                e($label),
                nl2br(e($val), false)
            );
        }

        return sprintf(
            '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"></head><body style="margin:0;padding:24px;background:#f3fbff;font-family:Segoe UI,Arial,sans-serif;color:#001f28;">'
            . '<div style="max-width:620px;margin:0 auto;background:#ffffff;border:1px solid #bcc9cb;border-radius:14px;overflow:hidden;">'
            . '<div style="background:#006875;color:#ffffff;padding:18px 24px;">'
            . '<div style="font-size:12px;letter-spacing:0.06em;text-transform:uppercase;opacity:0.9;">Hospital San Marcos • Pasaje, El Oro</div>'
            . '<h1 style="margin:6px 0 0;font-size:20px;font-weight:700;">%s</h1>'
            . '</div>'
            . '<div style="padding:22px 24px;">'
            . '<p style="margin:0 0 16px;font-size:15px;line-height:1.5;">%s</p>'
            . '<table style="width:100%%;border-collapse:collapse;font-size:14px;background:#f3fbff;border-radius:8px;">%s</table>'
            . '<p style="margin:18px 0 0;font-size:13px;line-height:1.5;color:#3d494b;">%s</p>'
            . '</div>'
            . '</div></body></html>',
            e($heading),
            $introHtml,
            $trs,
            e($footerNote)
        );
    }

    /**
     * @param array<string, string> $rows
     */
    private static function buildPlainText(string $title, array $rows): string
    {
        $lines = [$title, str_repeat('=', strlen($title)), ''];
        foreach ($rows as $label => $val) {
            $lines[] = $label . ': ' . $val;
        }
        $lines[] = '';
        $lines[] = 'Hospital San Marcos — ' . HOSPITAL_ADDRESS;
        return implode("\r\n", $lines);
    }

    private static function sanitizeHeader(string $value): string
    {
        return trim((string) preg_replace('/[\r\n\x00]+/', ' ', $value));
    }

    /**
     * @return array<string, mixed>
     */
    private static function getConfig(): array
    {
        if (self::$config === null) {
            $cfgFile = dirname(__DIR__, 2) . '/config/mail.php';
            if (!is_file($cfgFile)) {
                $cfgFile = dirname(__DIR__, 2) . '/config/mail.example.php';
            }
            self::$config = require $cfgFile;
        }
        return self::$config;
    }
}
