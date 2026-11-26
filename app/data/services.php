<?php
declare(strict_types=1);

/**
 * Catálogo oficial de servicios hospitalarios y fichas operativas del Hospital San Marcos
 * Incluye 6 servicios en cuadrícula y 4 fichas operativas detalladas
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
            'detailKey'   => 'emergencias',
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
            'detailKey'   => 'consulta',
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
            'detailKey'   => 'cirugia',
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
            'detailKey'   => 'ginecologia',
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
            'detailKey'   => 'laboratorio',
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
            'detailKey'   => 'imagen',
        ],
    ],
    'detailsMap' => [
        'laboratorio' => [
            'title'      => 'Laboratorio Clínico Automatizado',
            'tag'        => 'Unidad Diagnóstica Nivel II',
            'desc'       => 'Área técnica de bioanálisis equipada con analizadores hematológicos y bioquímicos con calibración diaria y control de calidad internacional.',
            'icon'       => 'science',
            'scope'      => [
                'Hematología completa y coagulación',
                'Bioquímica sanguínea (glucosa, perfil lipídico, renal, hepático)',
                'Inmunología, hormonas y marcadores tiroideos',
                'Microbiología, urocultivos y coproanálisis',
            ],
            'prep'       => [
                'Ayuno requerido de 8 a 12 horas para pruebas metabólicas',
                'Evitar ingesta de alcohol y grasas la noche anterior',
                'Presentar cédula de identidad y orden médica si aplica',
                'Notificar toma de medicamentos continuos al personal',
            ],
            'timeNotice' => 'Rutina: 2 a 4 horas • Especiales: 24h',
            'prepNotice' => 'Disponible en zona urbana de Pasaje con previa cita',
        ],
        'imagen' => [
            'title'      => 'Imagenología y Radiodiagnóstico Digital',
            'tag'        => 'Tecnología de Diagnóstico Visual',
            'desc'       => 'Equipos de baja radiación y ecografía doppler de alta resolución con interpretación oportuna de médicos radiólogos certificados.',
            'icon'       => 'radiology',
            'scope'      => [
                'Rayos X digitales de tórax, óseos y articulaciones',
                'Ecografías abdominales, pélvicas, renales y de tejidos blandos',
                'Ecografía obstétrica estructural y Doppler feto-placentario',
                'Tomografía axial computarizada programada',
            ],
            'prep'       => [
                'Retirar joyas, hebillas u objetos metálicos previo a rayos X',
                'Ecografía pélvica/renal: vejiga llena (beber agua 45 min antes)',
                'Ecografía abdominal superior: 6 horas de ayuno',
                'Traer estudios previos para análisis comparativo',
            ],
            'timeNotice' => 'Informes inmediatos para urgencias • Programados: 2 a 6 horas',
            'prepNotice' => 'Protocolo de baja dosis para seguridad del paciente pediátrico y adulto',
        ],
        'cirugia' => [
            'title'      => 'Cirugía General y Laparoscópica Mínimamente Invasiva',
            'tag'        => 'Centro Quirúrgico Acreditado',
            'desc'       => 'Quirófano moderno equipado con torres laparoscópicas HD, electrocauterios bipolares y monitorización anestesiológica continua.',
            'icon'       => 'precision_manufacturing',
            'scope'      => [
                'Colecistectomía laparoscópica (extracción de vesícula)',
                'Apendicectomía y plastias de pared abdominal (hernias)',
                'Cirugía ginecológica y cesáreas programadas o de urgencia',
                'Procedimientos ambulatorios y biopsias quirúrgicas',
            ],
            'prep'       => [
                'Valoración preanestésica obligatoria previa a la cirugía',
                'Ayuno absoluto de 8 horas antes del ingreso a quirófano',
                'Consentimiento informado firmado por el paciente o familiar',
                'Disponer de un acompañante adulto responsable para el alta',
            ],
            'timeNotice' => 'Quirófanos disponibles 24/7 para emergencias quirúrgicas',
            'prepNotice' => 'Recuperación asistida con enfermería especializada y analgesia continua',
        ],
        'hospitalizacion' => [
            'title'      => 'Hospitalización y Recuperación Asistida',
            'tag'        => 'Cuidado Clínico y Seguridad',
            'desc'       => 'Habitaciones individuales y compartidas con diseño ergonómico, climatización médica, gases medicinales y enfermería permanente.',
            'icon'       => 'bed',
            'scope'      => [
                'Manejo de postoperatorio inmediato y mediato',
                'Tratamientos antibióticos intravenosos y sueroterapia',
                'Control cardiorrespiratorio y hemodinámico continuo',
                'Atención integral del puerperio y recién nacido',
            ],
            'prep'       => [
                'Artículos de aseo personal y ropa cómoda de descanso',
                'Documento de identidad y carpeta con historial médico previo',
                'Registro de un familiar titular para informes médicos diarios',
                'Cumplimiento de las normas de bioseguridad institucional',
            ],
            'timeNotice' => 'Monitoreo clínico permanente las 24 horas del día',
            'prepNotice' => 'Acceso inclusivo, zona de descanso para acompañante y climatización',
        ],
    ],
];
