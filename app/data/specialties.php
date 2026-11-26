<?php
declare(strict_types=1);

/**
 * Catálogo oficial de especialidades médicas del Hospital San Marcos (17 especialidades)
 */

if (!defined('HSM_APP')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

return [
    [
        'id'          => 'ginecologia-y-obstetricia',
        'name'        => 'Ginecología y obstetricia',
        'description' => 'Salud femenina, control prenatal, partos y patologías reproductivas.',
        'badge'       => 'Maternidad',
        'icon'        => 'pregnant_woman',
    ],
    [
        'id'          => 'neurocirugia',
        'name'        => 'Neurocirugía',
        'description' => 'Diagnóstico y manejo de afecciones del sistema nervioso central y columna vertebral.',
        'badge'       => 'Neurociencias',
        'icon'        => 'psychology',
    ],
    [
        'id'          => 'medicina-interna',
        'name'        => 'Medicina interna',
        'description' => 'Diagnóstico y tratamiento integral de enfermedades del adulto y afecciones complejas.',
        'badge'       => 'Adultos',
        'icon'        => 'stethoscope',
    ],
    [
        'id'          => 'otorrinolaringologia',
        'name'        => 'Otorrinolaringología',
        'description' => 'Afecciones de oído, nariz, garganta y vías respiratorias superiores.',
        'badge'       => 'ORL',
        'icon'        => 'hearing',
    ],
    [
        'id'            => 'cirugia-general',
        'name'          => 'Cirugía general',
        'description'   => 'Procedimientos quirúrgicos del aparato digestivo, pared abdominal y patologías quirúrgicas.',
        'badge'         => 'Laparoscopía',
        'badgeType'     => 'highlight',
        'icon'          => 'medical_services',
        'highlightText' => 'Procedimientos de cirugía laparoscópica disponibles',
    ],
    [
        'id'          => 'gastroenterologia',
        'name'        => 'Gastroenterología',
        'description' => 'Diagnóstico y tratamiento del aparato digestivo, endoscopia y salud hepática.',
        'badge'       => 'Endoscopia',
        'icon'        => 'qr_code',
    ],
    [
        'id'          => 'cardiologia',
        'name'        => 'Cardiología',
        'description' => 'Prevención, diagnóstico y tratamiento de enfermedades cardiovasculares e hipertensión.',
        'badge'       => 'Cardiovascular',
        'icon'        => 'favorite',
    ],
    [
        'id'          => 'cirugia-estetico-maxilofacial',
        'name'        => 'Cirugía estético-maxilofacial',
        'description' => 'Corrección y reconstrucción funcional y estética facial y maxilar.',
        'badge'       => 'Reconstructiva',
        'icon'        => 'face',
    ],
    [
        'id'          => 'traumatologia',
        'name'        => 'Traumatología',
        'description' => 'Manejo de lesiones osteoarticulares, fracturas, esguinces y afecciones del aparato locomotor.',
        'badge'       => 'Osteoarticular',
        'icon'        => 'orthopedics',
    ],
    [
        'id'          => 'medicina-familiar',
        'name'        => 'Medicina familiar',
        'description' => 'Atención médica continua e integral para todos los miembros de la familia.',
        'badge'       => 'Atención Continua',
        'icon'        => 'family_restroom',
    ],
    [
        'id'          => 'diabetologia',
        'name'        => 'Diabetología',
        'description' => 'Manejo especializado, control metabólico y prevención de complicaciones de la diabetes.',
        'badge'       => 'Metabolismo',
        'icon'        => 'bloodtype',
    ],
    [
        'id'          => 'geriatria',
        'name'        => 'Geriatría',
        'description' => 'Atención especializada para adultos mayores, promoción de autonomía y manejo geriátrico integral.',
        'badge'       => 'Adulto Mayor',
        'icon'        => 'elderly',
    ],
    [
        'id'          => 'psicologia',
        'name'        => 'Psicología',
        'description' => 'Acompañamiento psicoterapéutico y salud emocional para niños, jóvenes y adultos.',
        'badge'       => 'Bienestar Emocional',
        'icon'        => 'self_improvement',
    ],
    [
        'id'          => 'psiquiatria',
        'name'        => 'Psiquiatría',
        'description' => 'Diagnóstico y tratamiento médico de trastornos de salud mental y del estado de ánimo.',
        'badge'       => 'Salud Mental',
        'icon'        => 'neurology',
    ],
    [
        'id'          => 'urologia',
        'name'        => 'Urología',
        'description' => 'Tratamiento del sistema urinario masculino/femenino y aparato reproductor masculino.',
        'badge'       => 'Urológico',
        'icon'        => 'vital_signs',
    ],
    [
        'id'          => 'cirugia-pediatrica',
        'name'        => 'Cirugía pediátrica',
        'description' => 'Procedimientos quirúrgicos especializados y adaptados a pacientes en edad pediátrica.',
        'badge'       => 'Pediatría',
        'badgeType'   => 'pediatria',
        'icon'        => 'child_care',
    ],
    [
        'id'          => 'oftalmologia',
        'name'        => 'Oftalmología',
        'description' => 'Salud visual integral, diagnóstico de patologías oculares y prescripción refractiva.',
        'badge'       => 'Salud Visual',
        'icon'        => 'visibility',
    ],
];
