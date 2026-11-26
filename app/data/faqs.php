<?php
declare(strict_types=1);

/**
 * Catálogo oficial de preguntas frecuentes del Hospital San Marcos (5 preguntas frecuentes)
 */

if (!defined('HSM_APP')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

return [
    [
        'id'       => 'emergencias-24h',
        'question' => '¿Cómo funciona el servicio de emergencias 24 horas?',
        'answer'   => 'Nuestro servicio de emergencias opera de manera ininterrumpida los 365 días del año con médico de guardia residente, enfermería y laboratorio de respuesta inmediata. El ingreso se realiza mediante triaje clínico estandarizado para priorizar según la gravedad del cuadro de salud. También disponemos de ambulancia para rescate y traslados dentro y fuera de Pasaje llamando al 098 797 3511.',
    ],
    [
        'id'       => 'preparacion-examenes',
        'question' => '¿Qué preparación previa necesito para exámenes de laboratorio e imagen?',
        'answer'   => 'Para la mayoría de exámenes sanguíneos se requiere entre 8 y 12 horas de ayuno estricto (agua simple está permitida). En ecografías pélvicas o renales se recomienda beber 1 litro de agua sin gas 45 minutos antes del estudio sin orinar. Para radiografías digitales no se requieren preparaciones digestivas, salvo retirar objetos metálicos. Puedes consultar las indicaciones exactas de tu prescripción vía WhatsApp previo a tu visita.',
    ],
    [
        'id'       => 'cirugia-programada',
        'question' => '¿Cómo solicito una valoración para cirugía programada?',
        'answer'   => 'El primer paso es agendar una consulta médica de valoración en consulta externa con el cirujano especialista. Durante esta cita se revisarán tus exámenes clínicos previos o se solicitarán los estudios preoperatorios (laboratorio, electrocardiograma y valoración anestesiológica). Una vez aprobado el perfil quirúrgico, se programa la fecha de quirófano que mejor se ajuste a tu tiempo.',
    ],
    [
        'id'       => 'horarios-visita',
        'question' => '¿Cuáles son los horarios de visita en el área de hospitalización?',
        'answer'   => 'Las visitas generales están habilitadas de 10:00 a 12:00 y de 16:00 a 18:00 para garantizar el reposo clínico de los pacientes. En habitaciones privadas se autoriza un acompañante permanente las 24 horas del día. Solicitamos a los visitantes mantener el orden, sanitización de manos al ingreso y evitar el acceso con niños menores de 12 años en áreas de cuidado especial.',
    ],
    [
        'id'       => 'agendar-consulta',
        'question' => '¿Cómo puedo agendar una consulta médica externa?',
        'answer'   => 'Puedes solicitar tu turno médico a través de nuestras vías oficiales: de forma presencial en recepción en nuestra sede central en Pasaje (Av. Quito y Sucre), por mensajería instantánea en nuestra línea oficial de WhatsApp (098 797 3511), o mediante la central telefónica. Te asignaremos fecha, hora y confirmación de agenda con el especialista.',
    ],
];
