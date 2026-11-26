<?php
declare(strict_types=1);

/**
 * Validador backend del formulario de Contacto Institucional
 */

if (!defined('HSM_APP')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

class ContactValidator
{
    public const SUBJECTS = [
        'consulta-general' => 'Consulta general y orientación',
        'admision-citas'   => 'Admisión y citas con especialistas',
        'servicios'        => 'Información de servicios de laboratorio y diagnóstico',
        'convenios'        => 'Convenios aseguradoras y corporativos',
        'sugerencias'      => 'Sugerencias o felicitaciones',
    ];

    /**
     * @param array<string, mixed> $input
     * @return array{
     *   valid: bool,
     *   errors: array<string, string>,
     *   old: array<string, string>,
     *   clean: array<string, mixed>
     * }
     */
    public static function validate(array $input): array
    {
        $rawName    = self::cleanSingleLine($input['nombre'] ?? '', 160);
        $rawEmail   = self::cleanSingleLine($input['email'] ?? '', 160);
        $rawPhone   = self::cleanSingleLine($input['telefono'] ?? '', 35);
        $rawSubject = self::cleanSingleLine($input['asunto'] ?? '', 60);
        $rawMessage = self::cleanMultiLine($input['mensaje'] ?? '');
        $rawPrivacy = isset($input['datos']) && in_array((string) $input['datos'], ['1', 'on', 'true'], true) ? '1' : '';

        $old = [
            'nombre'   => $rawName,
            'email'    => $rawEmail,
            'telefono' => $rawPhone,
            'asunto'   => $rawSubject,
            'mensaje'  => $rawMessage,
            'datos'    => $rawPrivacy,
        ];

        $errors = [];

        $normalizedName = trim((string) preg_replace('/\s+/u', ' ', $rawName));
        if ($normalizedName === '' || mb_strlen($normalizedName, 'UTF-8') < 3 || mb_strlen($normalizedName, 'UTF-8') > 150) {
            $errors['nombre'] = 'Ingresa tu nombre completo.';
        } elseif (preg_match('/^[\p{L}\p{M}][\p{L}\p{M}\s\'\.\-]+$/u', $normalizedName) !== 1) {
            $errors['nombre'] = 'El nombre solo debe contener letras y espacios.';
        } else {
            $old['nombre'] = $normalizedName;
        }

        $cleanEmail = '';
        if ($rawEmail === '' || mb_strlen($rawEmail, 'UTF-8') > 150 || filter_var($rawEmail, FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'] = 'Ingresa un correo electrónico válido para poder responderte.';
        } else {
            $cleanEmail   = strtolower($rawEmail);
            $old['email'] = $cleanEmail;
        }

        $cleanPhone = null;
        if ($rawPhone !== '') {
            $phoneCheck = AppointmentValidator::validatePhone($rawPhone);
            if (!$phoneCheck['valid']) {
                $errors['telefono'] = (string) $phoneCheck['error'];
            } else {
                $cleanPhone      = $phoneCheck['normalized'];
                $old['telefono'] = $cleanPhone;
            }
        }

        $subjectLabel = '';
        if ($rawSubject === '' || !isset(self::SUBJECTS[$rawSubject])) {
            $errors['asunto'] = 'Selecciona un asunto institucional de la lista.';
        } else {
            $subjectLabel = self::SUBJECTS[$rawSubject];
        }

        $msgLen = mb_strlen($rawMessage, 'UTF-8');
        if ($msgLen < 10) {
            $errors['mensaje'] = 'Escribe un mensaje con al menos 10 caracteres para detallar tu consulta.';
        } elseif ($msgLen > 1500) {
            $errors['mensaje'] = 'El mensaje no debe superar los 1500 caracteres.';
        }

        if ($rawPrivacy !== '1') {
            $errors['datos'] = 'Debes aceptar el tratamiento de datos personales para enviar tu mensaje.';
        }

        return [
            'valid'  => $errors === [],
            'errors' => $errors,
            'old'    => $old,
            'clean'  => [
                'name'          => $normalizedName,
                'email'         => $cleanEmail,
                'phone'         => $cleanPhone,
                'subject'       => $rawSubject,
                'subject_label' => $subjectLabel,
                'message'       => $rawMessage,
            ],
        ];
    }

    private static function cleanSingleLine(mixed $value, int $maxLen = 200): string
    {
        if (!is_string($value)) {
            return '';
        }
        $clean = strip_tags($value);
        $clean = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $clean) ?? '';
        $clean = trim($clean);
        if (mb_strlen($clean, 'UTF-8') > $maxLen) {
            $clean = mb_substr($clean, 0, $maxLen, 'UTF-8');
        }
        return $clean;
    }

    private static function cleanMultiLine(mixed $value): string
    {
        if (!is_string($value)) {
            return '';
        }
        $clean = strip_tags($value);
        $clean = str_replace(["\r\n", "\r"], "\n", $clean);
        $clean = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/u', '', $clean) ?? '';
        return trim($clean);
    }
}
