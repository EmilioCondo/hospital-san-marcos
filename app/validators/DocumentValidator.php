<?php
declare(strict_types=1);

/**
 * Validador de documento de identidad del paciente:
 * - Exclusivamente Cédula ecuatoriana (10 dígitos con provincia válida y dígito verificador módulo 10)
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
        // Eliminar espacios y guiones intermedios
        $cleaned = (string) preg_replace('/[\s\-]+/u', '', trim($raw));

        if ($cleaned === '') {
            return [
                'valid'      => false,
                'type'       => 'cedula',
                'normalized' => '',
                'error'      => 'Ingresa el número de cédula ecuatoriana del paciente.',
            ];
        }

        if (preg_match('/^\d{10}$/', $cleaned) !== 1) {
            return [
                'valid'      => false,
                'type'       => 'cedula',
                'normalized' => $cleaned,
                'error'      => 'Ingresa una cédula ecuatoriana válida de 10 dígitos.',
            ];
        }

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
            'error'      => 'El número de cédula ecuatoriana no es válido. Verifica los 10 dígitos.',
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
