<?php
declare(strict_types=1);

/**
 * Validador de documento de identidad del paciente:
 * - Cédula ecuatoriana (10 dígitos con provincia válida y dígito verificador módulo 10)
 * - Pasaporte (alfanumérico internacional de 5 a 20 caracteres)
 */

if (!defined('HSM_APP')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

class DocumentValidator
{
    /**
     * @return array{valid: bool, type: string, normalized: string, error: string|null}
     */
    public static function validate(string $raw): array
    {
        // Eliminar espacios y guiones intermedios y convertir a mayúsculas
        $cleaned = strtoupper((string) preg_replace('/[\s\-]+/u', '', trim($raw)));

        if ($cleaned === '') {
            return [
                'valid'      => false,
                'type'       => 'cedula',
                'normalized' => '',
                'error'      => 'Ingresa el número de cédula o pasaporte del paciente.',
            ];
        }

        // Caso 1: Exactamente 10 dígitos numéricos -> Validar como cédula ecuatoriana
        if (preg_match('/^\d{10}$/', $cleaned) === 1) {
            if (self::isValidEcuadorianCedula($cleaned)) {
                return [
                    'valid'      => true,
                    'type'       => 'cedula',
                    'normalized' => $cleaned,
                    'error'      => null,
                ];
            }

            return [
                'valid'      => false,
                'type'       => 'cedula',
                'normalized' => $cleaned,
                'error'      => 'El número de cédula ecuatoriana no es válido. Verifica los 10 dígitos o ingresa tu pasaporte.',
            ];
        }

        // Caso 2: Pasaporte (alfanumérico de 5 a 20 caracteres, o numérico extranjero de longitud distinta de 10)
        if (preg_match('/^[A-Z0-9]{5,20}$/', $cleaned) === 1 && preg_match('/^0+$/', $cleaned) !== 1) {
            return [
                'valid'      => true,
                'type'       => 'pasaporte',
                'normalized' => $cleaned,
                'error'      => null,
            ];
        }

        return [
            'valid'      => false,
            'type'       => 'cedula',
            'normalized' => $cleaned,
            'error'      => 'Ingresa una cédula ecuatoriana (10 dígitos) o un pasaporte válido (5 a 20 caracteres).',
        ];
    }

    public static function isValidEcuadorianCedula(string $cedula): bool
    {
        if (preg_match('/^\d{10}$/', $cedula) !== 1) {
            return false;
        }

        $province = (int) substr($cedula, 0, 2);
        if (($province < 1 || $province > 24) && $province !== 30) {
            return false;
        }

        $coefficients = [2, 1, 2, 1, 2, 1, 2, 1, 2];
        $sum = 0;

        for ($i = 0; $i < 9; $i++) {
            $val = ((int) $cedula[$i]) * $coefficients[$i];
            if ($val >= 10) {
                $val -= 9;
            }
            $sum += $val;
        }

        $checkDigit = (10 - ($sum % 10)) % 10;
        return $checkDigit === (int) $cedula[9];
    }
}
