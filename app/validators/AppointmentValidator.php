<?php
declare(strict_types=1);

/**
 * Validador backend de solicitudes de citas médicas (4 pasos)
 * Trabaja exclusivamente con el catálogo de `especialidades` y `medicos` (o Sin preferencia).
 */

if (!defined('HSM_APP')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

class AppointmentValidator
{
    /**
     * @param array<string, mixed> $input
     * @return array{
     *   valid: bool,
     *   errors: array<string, string>,
     *   errorStep: int,
     *   old: array<string, string>,
     *   clean: array<string, mixed>
     * }
     */
    public static function validate(array $input): array
    {
        $rawSpecialty = self::cleanSingleLine($input['especialidad'] ?? '', 120);
        $rawDoctor    = self::cleanSingleLine($input['medico'] ?? '', 150);
        $rawDate      = self::cleanSingleLine($input['fecha'] ?? '', 20);
        $rawShift     = self::cleanSingleLine($input['shift-preference'] ?? '', 50);
        $rawName      = self::cleanSingleLine($input['paciente_nombre'] ?? '', 160);
        $rawDocument  = self::cleanSingleLine($input['paciente_documento'] ?? '', 30);
        $rawPhone     = self::cleanSingleLine($input['paciente_telefono'] ?? '', 35);
        $rawEmail     = self::cleanSingleLine($input['paciente_email'] ?? '', 160);
        $rawReason    = self::cleanMultiLine($input['motivo'] ?? '');
        $rawPrivacy   = isset($input['privacidad']) && in_array((string) $input['privacidad'], ['1', 'on', 'true'], true) ? '1' : '';

        $old = [
            'especialidad'       => $rawSpecialty,
            'medico'             => $rawDoctor,
            'fecha'              => $rawDate,
            'shift-preference'   => $rawShift !== '' ? $rawShift : 'Mañana (08:00 - 12:00)',
            'paciente_nombre'    => $rawName,
            'paciente_documento' => $rawDocument,
            'paciente_telefono'  => $rawPhone,
            'paciente_email'     => $rawEmail,
            'motivo'             => $rawReason,
            'privacidad'         => $rawPrivacy,
        ];

        $errors = [];

        // -----------------------------------------------------------------
        // PASO 1: Especialidad (solo catálogo `especialidades`) y Médico (`medicos` o Sin preferencia)
        // -----------------------------------------------------------------
        $specialtyDbId  = null;
        $specialtySlug  = null;
        $attentionLabel = '';

        if ($rawSpecialty === '') {
            $errors['especialidad'] = 'Selecciona una especialidad médica.';
        } else {
            $matchedSpec = SpecialtyRepository::findForBooking($rawSpecialty);
            if ($matchedSpec === null) {
                $errors['especialidad'] = 'La especialidad seleccionada no pertenece al catálogo institucional.';
            } else {
                $specialtyDbId       = $matchedSpec['db_id'];
                $specialtySlug       = $matchedSpec['id'];
                $attentionLabel      = $matchedSpec['name'];
                $old['especialidad'] = $matchedSpec['id'];
            }
        }

        $doctorDbId  = null;
        $doctorLabel = 'Sin preferencia (Cualquier especialista disponible)';

        if ($rawDoctor === '' || $rawDoctor === 'sin-preferencia' || strcasecmp($rawDoctor, 'Cualquier especialista disponible') === 0) {
            $old['medico'] = '';
        } else {
            $matchedDoc = DoctorRepository::findForBooking($rawDoctor);
            if ($matchedDoc === null) {
                $errors['medico'] = 'El médico seleccionado no es válido.';
            } elseif ($specialtySlug !== null) {
                $docSpecs = $matchedDoc['specialty_slugs'] ?? [];
                if (!in_array($specialtySlug, $docSpecs, true)) {
                    $errors['medico'] = 'El médico elegido no corresponde a la especialidad seleccionada.';
                } else {
                    $doctorDbId    = (int) $matchedDoc['db_id'];
                    $doctorLabel   = $matchedDoc['name'] . ' — ' . $matchedDoc['specialty'];
                    $old['medico'] = (string) $matchedDoc['id'];
                }
            }
        }

        // -----------------------------------------------------------------
        // PASO 2: Fecha (hoy .. +90 días) y Turno
        // -----------------------------------------------------------------
        $tz      = new DateTimeZone('America/Guayaquil');
        $today   = new DateTimeImmutable('today', $tz);
        $maxDate = $today->modify('+90 days');

        $appointmentDate = '';
        if ($rawDate === '' || preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawDate) !== 1) {
            $errors['fecha'] = 'Selecciona una fecha válida para tu cita.';
        } else {
            $parsedDate = DateTimeImmutable::createFromFormat('!Y-m-d', $rawDate, $tz);
            $dateErrors = DateTimeImmutable::getLastErrors();
            $hasDateWarnings = is_array($dateErrors) && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0);

            if ($parsedDate === false || $hasDateWarnings || $parsedDate->format('Y-m-d') !== $rawDate) {
                $errors['fecha'] = 'La fecha ingresada no es válida.';
            } elseif ($parsedDate < $today) {
                $errors['fecha'] = 'La fecha de la cita no puede ser anterior al día de hoy.';
            } elseif ($parsedDate > $maxDate) {
                $errors['fecha'] = 'Solo puedes solicitar citas hasta con 90 días de anticipación.';
            } else {
                $appointmentDate = $parsedDate->format('Y-m-d');
            }
        }

        $shiftDb    = '';
        $shiftLabel = '';
        $shiftNorm  = mb_strtolower($rawShift, 'UTF-8');
        if ($shiftNorm === 'manana' || str_starts_with($shiftNorm, 'mañana') || $shiftNorm === 'matutino') {
            $shiftDb                 = 'matutino';
            $shiftLabel              = 'Mañana (08:00 - 12:00)';
            $old['shift-preference'] = $shiftLabel;
        } elseif ($shiftNorm === 'tarde' || str_starts_with($shiftNorm, 'tarde') || $shiftNorm === 'vespertino') {
            $shiftDb                 = 'vespertino';
            $shiftLabel              = 'Tarde (14:00 - 18:00)';
            $old['shift-preference'] = $shiftLabel;
        } else {
            $errors['turno'] = 'Selecciona una franja horaria válida (Mañana o Tarde).';
        }

        // -----------------------------------------------------------------
        // PASO 3: Datos del paciente y consentimiento
        // -----------------------------------------------------------------
        $normalizedName = trim((string) preg_replace('/\s+/u', ' ', $rawName));
        $nameWords      = $normalizedName === '' ? [] : explode(' ', $normalizedName);
        if ($normalizedName === '' || mb_strlen($normalizedName, 'UTF-8') < 4 || mb_strlen($normalizedName, 'UTF-8') > 150) {
            $errors['paciente_nombre'] = 'Ingresa los nombres y apellidos completos del paciente.';
        } elseif (count($nameWords) < 2 || preg_match('/^[\p{L}\p{M}][\p{L}\p{M}\s\'\.\-]+$/u', $normalizedName) !== 1) {
            $errors['paciente_nombre'] = 'Ingresa al menos un nombre y un apellido usando solo letras.';
        } else {
            $old['paciente_nombre'] = $normalizedName;
        }

        $docCheck = DocumentValidator::validate($rawDocument);
        if (!$docCheck['valid']) {
            $errors['paciente_documento'] = (string) $docCheck['error'];
        } else {
            $old['paciente_documento'] = $docCheck['normalized'];
        }

        $phoneCheck = self::validatePhone($rawPhone);
        if (!$phoneCheck['valid']) {
            $errors['paciente_telefono'] = (string) $phoneCheck['error'];
        } else {
            $old['paciente_telefono'] = $phoneCheck['normalized'];
        }

        $cleanEmail = null;
        if ($rawEmail !== '') {
            if (mb_strlen($rawEmail, 'UTF-8') > 150 || filter_var($rawEmail, FILTER_VALIDATE_EMAIL) === false) {
                $errors['paciente_email'] = 'Ingresa un correo electrónico válido o déjalo vacío.';
            } else {
                $cleanEmail            = strtolower($rawEmail);
                $old['paciente_email'] = $cleanEmail;
            }
        }

        $cleanReason = null;
        if ($rawReason !== '') {
            if (mb_strlen($rawReason, 'UTF-8') > 500) {
                $errors['motivo'] = 'El motivo o comentario no debe superar los 500 caracteres.';
            } else {
                $cleanReason = $rawReason;
            }
        }

        if ($rawPrivacy !== '1') {
            $errors['privacidad'] = 'Debes aceptar la política de protección de datos personales para registrar la solicitud.';
        }

        $errorStep = 1;
        if ($errors !== []) {
            if (isset($errors['especialidad']) || isset($errors['medico'])) {
                $errorStep = 1;
            } elseif (isset($errors['fecha']) || isset($errors['turno'])) {
                $errorStep = 2;
            } else {
                $errorStep = 3;
            }
        }

        return [
            'valid'     => $errors === [],
            'errors'    => $errors,
            'errorStep' => $errorStep,
            'old'       => $old,
            'clean'     => [
                'specialty_id'     => $specialtyDbId,
                'attention_label'  => $attentionLabel,
                'doctor_id'        => $doctorDbId,
                'doctor_label'     => $doctorLabel,
                'patient_doc_type' => $docCheck['type'],
                'patient_id_doc'   => $docCheck['normalized'],
                'patient_name'     => $normalizedName,
                'patient_phone'    => $phoneCheck['normalized'],
                'patient_email'    => $cleanEmail,
                'appointment_date' => $appointmentDate,
                'shift'            => $shiftDb,
                'shift_label'      => $shiftLabel,
                'reason'           => $cleanReason,
            ],
        ];
    }

    /**
     * @return array{valid: bool, normalized: string, error: string|null}
     */
    public static function validatePhone(string $raw): array
    {
        $compact = (string) preg_replace('/[\s\-\(\)\.]+/u', '', trim($raw));
        if ($compact === '') {
            return [
                'valid'      => false,
                'normalized' => '',
                'error'      => 'Ingresa un número de teléfono móvil o convencional de contacto.',
            ];
        }

        if (preg_match('/^\+?593([2-9]\d{7,8})$/', $compact, $m) === 1) {
            return [
                'valid'      => true,
                'normalized' => '0' . $m[1],
                'error'      => null,
            ];
        }

        if (preg_match('/^0[2-9]\d{7,8}$/', $compact) === 1) {
            return [
                'valid'      => true,
                'normalized' => $compact,
                'error'      => null,
            ];
        }

        if (preg_match('/^\+[1-9]\d{7,14}$/', $compact) === 1) {
            return [
                'valid'      => true,
                'normalized' => $compact,
                'error'      => null,
            ];
        }

        return [
            'valid'      => false,
            'normalized' => $raw,
            'error'      => 'Ingresa un teléfono ecuatoriano válido (ej. 098 765 4321) o con código internacional (+...).',
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
