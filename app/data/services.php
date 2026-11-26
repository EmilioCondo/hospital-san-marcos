<?php
declare(strict_types=1);

/**
 * Catálogo oficial de servicios hospitalarios del Hospital San Marcos (6 tarjetas principales)
 */

if (!defined('HSM_APP')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

return [
    'services' => [
        [
            'id'          => 'emergencias-24h',
            'name'        => 'Emergencias 24 horas y ambulancia',
            'tag'         => 'Atención 24/7',
            'tagClass'    => 'bg-tertiary text-on-tertiary font-bold uppercase',
            'description' => 'Atención médica inmediata para urgencias y emergencias con equipo de guardia y soporte móvil permanente.',
            'icon'        => 'emergency',
            'features'    => [
                'Unidad móvil de soporte vital disponible',
                'Triaje Manchester con respuesta inmediata',
            ],
        ],
        [
            'id'          => 'consulta-externa',
            'name'        => 'Consulta externa',
            'tag'         => 'Agenda programada',
            'tagClass'    => 'bg-surface-container-low text-primary font-semibold',
            'description' => 'Valoración médica integral con diversas especialidades para diagnóstico, seguimiento y tratamiento programado.',
            'icon'        => 'stethoscope',
            'features'    => [
                'Turnos matutinos y vespertinos',
                'Historia clínica digital centralizada',
            ],
        ],
        [
            'id'          => 'cirugia-general-laparoscopica',
            'name'        => 'Cirugía general y laparoscópica',
            'tag'         => 'Quirófano moderno',
            'tagClass'    => 'bg-surface-container-low text-primary font-semibold',
            'description' => 'Procedimientos quirúrgicos seguros y mínimamente invasivos con quirófano moderno y valoración prequirúrgica.',
            'icon'        => 'precision_manufacturing',
            'features'    => [
                'Recuperación ambulatoria y hospitalaria',
                'Anestesiología y monitorización avanzada',
            ],
        ],
        [
            'id'          => 'ginecologia-obstetricia',
            'name'        => 'Ginecología y obstetricia',
            'tag'         => 'Materno-Fetal',
            'tagClass'    => 'bg-surface-container-low text-primary font-semibold',
            'description' => 'Cuidado integral de la salud femenina, control prenatal, atención del parto y patologías ginecológicas.',
            'icon'        => 'pregnant_woman',
            'features'    => [
                'Monitoreo fetal y sala de parto humanizado',
                'Ecografía obstétrica y cribado preventivo',
            ],
        ],
        [
            'id'          => 'laboratorio-clinico',
            'name'        => 'Laboratorio clínico',
            'tag'         => 'Resultados rápidos',
            'tagClass'    => 'bg-secondary-container text-on-secondary-container font-semibold',
            'description' => 'Exámenes de rutina, pruebas especiales, hematología, bioquímica y microbiología con entrega oportuna de resultados.',
            'icon'        => 'science',
            'features'    => [
                'Entrega digital por correo y WhatsApp',
                'Toma de muestras desde las 06:30',
            ],
        ],
        [
            'id'          => 'imagenologia',
            'name'        => 'Imagenología',
            'tag'         => 'Diagnóstico visual',
            'tagClass'    => 'bg-surface-container-low text-primary font-semibold',
            'description' => 'Diagnóstico preciso con rayos X digitales, ecografías de alta resolución y tomografía axial computarizada.',
            'icon'        => 'radiology',
            'features'    => [
                'Baja radiación con sensor digital',
                'Informes avalados por radiólogos',
            ],
        ],
    ],
];
