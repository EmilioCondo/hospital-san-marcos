<?php
declare(strict_types=1);

if (!defined('HSM_APP')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

$currentPage          = 'Agendar cita';
$specialties          = SpecialtyRepository::getAll();
$bookingDoctors       = DoctorRepository::getAllForBooking();
$confirmedAppointment = Security::pullFlash(AppointmentController::FLASH_KEY);

$errors      = is_array($formFeedback['errors'] ?? null) ? $formFeedback['errors'] : [];
$old         = is_array($formFeedback['old'] ?? null) ? $formFeedback['old'] : [];
$initialStep = (int) ($formFeedback['errorStep'] ?? 1);

$tz          = new DateTimeZone('America/Guayaquil');
$todayDate   = (new DateTimeImmutable('today', $tz))->format('Y-m-d');
$defaultDate = (new DateTimeImmutable('+1 day', $tz))->format('Y-m-d');
$maxDate     = (new DateTimeImmutable('+90 days', $tz))->format('Y-m-d');

$doctorFromUrl    = sanitize_query_param($_GET['doctor'] ?? null, 80);
$specialtyFromUrl = sanitize_query_param($_GET['specialty'] ?? null, 80);

$selectedSpecialtySlug  = '';
$selectedSpecialtyLabel = 'Pendiente de seleccionar';
$selectedDoctorSlug     = '';
$selectedDoctorLabel    = 'Sin preferencia (Cualquier especialista disponible)';

if ($old !== []) {
    $selectedSpecialtySlug = (string) ($old['especialidad'] ?? '');
    $selectedDoctorSlug    = (string) ($old['medico'] ?? '');
} elseif ($doctorFromUrl !== '') {
    $matchedDoctor = DoctorRepository::findForBooking($doctorFromUrl);
    if ($matchedDoctor !== null && $matchedDoctor['primary_specialty_slug'] !== null) {
        $selectedDoctorSlug    = (string) $matchedDoctor['id'];
        $selectedDoctorLabel   = $matchedDoctor['name'] . ' — ' . $matchedDoctor['specialty'];
        $selectedSpecialtySlug = (string) $matchedDoctor['primary_specialty_slug'];
    }
} elseif ($specialtyFromUrl !== '') {
    $matchedSpec = SpecialtyRepository::findForBooking($specialtyFromUrl);
    if ($matchedSpec !== null) {
        $selectedSpecialtySlug = $matchedSpec['id'];
    }
}

if ($selectedSpecialtySlug !== '') {
    $specObj = SpecialtyRepository::findForBooking($selectedSpecialtySlug);
    if ($specObj !== null) {
        $selectedSpecialtyLabel = $specObj['name'];
    } else {
        $selectedSpecialtySlug = '';
    }
}

if ($selectedDoctorSlug !== '') {
    $docObj = DoctorRepository::findForBooking($selectedDoctorSlug);
    if ($docObj !== null) {
        $selectedDoctorLabel = $docObj['name'] . ' — ' . $docObj['specialty'];
    } else {
        $selectedDoctorSlug = '';
    }
}

$dateValue     = (string) ($old['fecha'] ?? $defaultDate);
$shiftValue    = (string) ($old['shift-preference'] ?? 'Mañana (08:00 - 12:00)');
$isEvening     = str_starts_with(mb_strtolower($shiftValue, 'UTF-8'), 'tarde');
$fullnameValue = (string) ($old['paciente_nombre'] ?? '');
$documentValue = (string) ($old['paciente_documento'] ?? '');
$phoneValue    = (string) ($old['paciente_telefono'] ?? '');
$emailValue    = (string) ($old['paciente_email'] ?? '');
$reasonValue   = (string) ($old['motivo'] ?? '');
$privacyChecked = ($old['privacidad'] ?? '') === '1';
?>
<div class="hospital-page-hero flex flex-col w-full">
  <!-- Top Breadcrumb & Hero Area -->
  <section class="relative w-full bg-gradient-to-b from-surface-container-low via-surface to-surface overflow-hidden pt-8 pb-12">
    <div class="absolute -top-24 right-0 w-96 h-96 bg-primary/5 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute top-1/2 left-4 w-72 h-72 bg-secondary-fixed/20 rounded-full blur-2xl pointer-events-none"></div>
    <div class="max-w-7xl mx-auto px-gutter relative z-10">
      <!-- Breadcrumb -->
      <?php require __DIR__ . '/../partials/breadcrumb.php'; ?>

      <div class="hospital-hero-grid grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        <div class="hospital-hero-copy lg:col-span-8 space-y-3">
          <h1 class="hospital-hero-title font-display text-display text-on-surface tracking-tight leading-tight">
            Agenda tu próxima cita médica.
          </h1>
          <p class="hospital-hero-description font-body-lg text-body-lg text-on-surface-variant max-w-2xl leading-relaxed">
            Solicita una consulta de manera rápida y sencilla. Nuestro equipo de admisión confirmará la disponibilidad y asignará tu horario oficial.
          </p>
        </div>
        <div class="lg:col-span-4 flex flex-col sm:flex-row lg:flex-col gap-3 justify-end">
          <div class="hospital-hero-card p-4 rounded-xl bg-surface-container-lowest shadow-sm flex items-center gap-4 border border-outline-variant">
            <div class="w-11 h-11 rounded-full bg-error-container text-on-error-container flex items-center justify-center shrink-0">
              <span class="material-symbols-outlined text-[22px]">e911_emergency</span>
            </div>
            <div>
              <span class="font-label-sm text-label-sm text-tertiary uppercase block">
                ¿Urgencia médica?
              </span>
              <span class="font-title-md text-title-md text-on-surface font-semibold">
                Emergencias 24 Horas activas
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Main Workflow & Assistant Section -->
  <section class="w-full pb-space-2xl">
    <div class="max-w-7xl mx-auto px-gutter">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        <!-- Left: Stepper + Form Multi-step Container (8 cols) -->
        <div class="lg:col-span-8 space-y-6">
          <?php if ($confirmedAppointment !== null): ?>
          <!-- TARJETA DE CONFIRMACIÓN REAL DE SOLICITUD DE CITA -->
          <div id="cita-confirmacion" role="status" class="bg-surface-container-lowest p-6 sm:p-10 rounded-2xl shadow-sm border border-outline-variant space-y-8 animate-fadeIn">
            <div class="border-b border-surface-container-high pb-6 space-y-3">
              <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-secondary-container text-on-secondary-container font-label-sm text-label-sm font-bold uppercase tracking-wider">
                <span class="material-symbols-outlined text-[16px]">check_circle</span>
                <span>Solicitud Registrada • Pendiente de Confirmación</span>
              </div>
              <h2 class="font-headline-md text-headline-md text-on-surface font-semibold">
                Hemos recibido tu solicitud de cita médica
              </h2>
              <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
                Conserva tu código de registro. Nuestro equipo de <strong>Admisión</strong> revisará la agenda del especialista y se comunicará contigo vía telefónica o WhatsApp para confirmar tu turno definitivo.
              </p>
            </div>

            <!-- Código único HCSM-XXXX -->
            <div class="p-6 rounded-2xl bg-surface-container-low border border-primary flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
              <div>
                <span class="font-label-sm text-label-sm text-outline uppercase font-bold block">
                  Código único de solicitud
                </span>
                <p id="confirmed-appointment-code" class="font-headline-md text-headline-md text-primary font-bold tracking-widest mt-1">
                  <?= e((string) $confirmedAppointment['code']) ?>
                </p>
              </div>
              <span class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-surface-container-lowest text-primary font-label-md text-label-md font-semibold border border-outline-variant">
                <span class="material-symbols-outlined text-[18px]">pending_actions</span>
                <span>Estado: Pendiente de asignar turno</span>
              </span>
            </div>

            <!-- Resumen de datos registrados -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="p-5 rounded-2xl bg-surface-container-low space-y-2 border border-outline-variant">
                <span class="font-label-sm text-label-sm text-outline uppercase font-bold block">
                  1. Atención Solicitada
                </span>
                <p class="font-title-md text-title-md font-bold text-on-surface">
                  <?= e((string) $confirmedAppointment['attention_label']) ?>
                </p>
                <p class="font-body-sm text-body-sm text-on-surface-variant">
                  <?= e((string) $confirmedAppointment['doctor_label']) ?>
                </p>
              </div>

              <div class="p-5 rounded-2xl bg-surface-container-low space-y-2 border border-outline-variant">
                <span class="font-label-sm text-label-sm text-outline uppercase font-bold block">
                  2. Fecha y Franja Preferida
                </span>
                <p class="font-title-md text-title-md font-bold text-on-surface">
                  <?= e((string) $confirmedAppointment['appointment_date']) ?>
                </p>
                <p class="font-body-sm text-body-sm text-on-surface-variant">
                  <?= e((string) $confirmedAppointment['shift_label']) ?>
                </p>
              </div>

              <div class="md:col-span-2 p-5 rounded-2xl bg-surface-container-low space-y-2 border border-outline-variant">
                <span class="font-label-sm text-label-sm text-outline uppercase font-bold block">
                  3. Datos del Paciente
                </span>
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                  <div class="space-y-1">
                    <p class="font-title-md text-title-md font-bold text-on-surface">
                      <?= e((string) $confirmedAppointment['patient_name']) ?>
                    </p>
                    <p class="font-body-sm text-body-sm text-on-surface-variant">
                      <?= ($confirmedAppointment['patient_doc_type'] ?? 'cedula') === 'pasaporte' ? 'Pasaporte' : 'Cédula' ?>: <?= e((string) $confirmedAppointment['patient_id_doc']) ?> • Teléfono: <?= e((string) $confirmedAppointment['patient_phone']) ?>
                    </p>
                  </div>
                  <div class="font-body-sm text-body-sm text-outline">
                    <?php if (!empty($confirmedAppointment['patient_email'])): ?>
                      Correo: <?= e((string) $confirmedAppointment['patient_email']) ?>
                    <?php else: ?>
                      Sin correo electrónico indicado
                    <?php endif; ?>
                  </div>
                </div>
                <?php if (!empty($confirmedAppointment['reason'])): ?>
                  <p class="font-body-sm text-body-sm text-on-surface-variant pt-2">
                    <strong>Motivo / Comentarios:</strong> <?= e((string) $confirmedAppointment['reason']) ?>
                  </p>
                <?php endif; ?>
              </div>
            </div>

            <?php if (!empty($confirmedAppointment['email_sent']) && !empty($confirmedAppointment['patient_email'])): ?>
              <div class="p-4 rounded-xl bg-surface-container-high flex items-start gap-3 border border-outline-variant">
                <span class="material-symbols-outlined text-primary text-[20px] shrink-0 mt-0.5">mail</span>
                <p class="font-body-sm text-body-sm text-on-surface leading-snug">
                  Enviamos una copia del comprobante de solicitud a <strong><?= e((string) $confirmedAppointment['patient_email']) ?></strong>.
                </p>
              </div>
            <?php endif; ?>

            <div class="pt-2 flex flex-col sm:flex-row items-center justify-between gap-4">
              <a
                href="<?= e(url('/')) ?>"
                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 text-on-surface-variant hover:text-primary font-title-md text-label-md font-semibold px-4 py-3 rounded-full transition-colors"
              >
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                <span>Volver al inicio</span>
              </a>
              <a
                href="<?= e(url('/agendar-cita')) ?>"
                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-primary text-on-primary font-title-md text-label-md font-bold px-8 py-3.5 rounded-full hover:bg-on-primary-container transition-all shadow-sm"
              >
                <span class="material-symbols-outlined text-[18px]">add_circle</span>
                <span>Registrar otra solicitud</span>
              </a>
            </div>
          </div>
          <?php else: ?>
          <!-- Stepper Navigation -->
          <div class="hospital-hero-card bg-surface-container-lowest p-5 sm:p-6 rounded-2xl shadow-sm border border-outline-variant">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 relative" role="tablist" aria-label="Pasos para agendar cita">
              <!-- Step 1 Indicator -->
              <button
                type="button"
                data-step-trigger="1"
                class="flex items-center gap-3 text-left transition-all p-2 rounded-xl group focus-primary bg-surface-container-low text-primary"
              >
                <div
                  data-step-circle="1"
                  class="w-9 h-9 rounded-full font-title-md text-label-md flex items-center justify-center font-bold shrink-0 transition-transform group-hover:scale-105 bg-primary text-on-primary"
                >
                  1
                </div>
                <div class="min-w-0">
                  <span class="font-label-sm text-label-sm text-outline uppercase block tracking-wider">
                    Paso 01
                  </span>
                  <span class="font-title-md text-body-sm font-bold text-on-surface truncate block">
                    Atención
                  </span>
                </div>
              </button>

              <!-- Step 2 Indicator -->
              <button
                type="button"
                data-step-trigger="2"
                class="flex items-center gap-3 text-left transition-all p-2 rounded-xl group focus-primary text-on-surface-variant hover:bg-surface-container-low"
              >
                <div
                  data-step-circle="2"
                  class="w-9 h-9 rounded-full font-title-md text-label-md flex items-center justify-center font-bold shrink-0 transition-transform group-hover:scale-105 bg-surface-container text-on-surface-variant"
                >
                  2
                </div>
                <div class="min-w-0">
                  <span class="font-label-sm text-label-sm text-outline uppercase block tracking-wider">
                    Paso 02
                  </span>
                  <span class="font-title-md text-body-sm font-semibold text-on-surface truncate block">
                    Fecha y turno
                  </span>
                </div>
              </button>

              <!-- Step 3 Indicator -->
              <button
                type="button"
                data-step-trigger="3"
                class="flex items-center gap-3 text-left transition-all p-2 rounded-xl group focus-primary text-on-surface-variant hover:bg-surface-container-low"
              >
                <div
                  data-step-circle="3"
                  class="w-9 h-9 rounded-full font-title-md text-label-md flex items-center justify-center font-bold shrink-0 transition-transform group-hover:scale-105 bg-surface-container text-on-surface-variant"
                >
                  3
                </div>
                <div class="min-w-0">
                  <span class="font-label-sm text-label-sm text-outline uppercase block tracking-wider">
                    Paso 03
                  </span>
                  <span class="font-title-md text-body-sm font-semibold text-on-surface truncate block">
                    Datos paciente
                  </span>
                </div>
              </button>

              <!-- Step 4 Indicator -->
              <button
                type="button"
                data-step-trigger="4"
                class="flex items-center gap-3 text-left transition-all p-2 rounded-xl group focus-primary text-on-surface-variant hover:bg-surface-container-low"
              >
                <div
                  data-step-circle="4"
                  class="w-9 h-9 rounded-full font-title-md text-label-md flex items-center justify-center font-bold shrink-0 transition-transform group-hover:scale-105 bg-surface-container text-on-surface-variant"
                >
                  4
                </div>
                <div class="min-w-0">
                  <span class="font-label-sm text-label-sm text-outline uppercase block tracking-wider">
                    Paso 04
                  </span>
                  <span class="font-title-md text-body-sm font-semibold text-on-surface truncate block">
                    Revisión
                  </span>
                </div>
              </button>
            </div>

            <!-- Progress Bar -->
            <div class="w-full bg-surface-container-high h-1.5 rounded-full mt-4 overflow-hidden">
              <div
                id="appointment-progress-bar"
                class="bg-primary-container h-full transition-all duration-300 w-1/4"
              ></div>
            </div>
          </div>

          <!-- Form Canvas Card -->
          <div class="bg-surface-container-lowest p-6 sm:p-10 rounded-2xl shadow-sm border border-outline-variant relative overflow-hidden">
            <form
              id="appointment-form"
              method="post"
              action="<?= e(url('/agendar-cita')) ?>"
              data-initial-step="<?= (int) $initialStep ?>"
              class="space-y-6"
              novalidate
            >
              <input type="hidden" name="_csrf" value="<?= e(Security::csrfToken()) ?>" />
              <div class="sr-only" aria-hidden="true">
                <label for="appointment-website-url">No completar este campo</label>
                <input type="text" id="appointment-website-url" name="website_url" value="" tabindex="-1" autocomplete="off" />
              </div>

              <?php if ($errors !== []): ?>
                <div id="appointment-server-errors" role="alert" class="p-4 rounded-xl bg-error-container text-on-error-container border border-outline-variant space-y-2">
                  <div class="flex items-center gap-2 font-title-md text-body-md font-bold">
                    <span class="material-symbols-outlined text-[20px]">error</span>
                    <span>Revisa los datos antes de continuar:</span>
                  </div>
                  <ul class="space-y-1 font-body-sm text-body-sm pl-6">
                    <?php foreach ($errors as $errMsg): ?>
                      <li>• <?= e($errMsg) ?></li>
                    <?php endforeach; ?>
                  </ul>
                </div>
              <?php endif; ?>

            <!-- STEP 1: ATENCIÓN -->
            <div data-step-panel="1" class="space-y-8 animate-fadeIn">
              <div class="border-b border-surface-container-high pb-4">
                <div class="flex items-center gap-2 text-primary font-label-md text-label-md font-bold uppercase tracking-wider mb-1">
                  <span class="material-symbols-outlined text-[18px]">medical_services</span>
                  <span>Etapa Inicial</span>
                </div>
                <h2 class="font-headline-sm text-headline-sm text-on-surface font-semibold">
                  Seleccionar atención médica
                </h2>
                <p class="font-body-sm text-body-sm text-on-surface-variant mt-1">
                  Elige el área clínica requerida y el médico de tu preferencia.
                </p>
              </div>

              <!-- Specialty Selection (alimentado exclusivamente desde SpecialtyRepository) -->
              <div class="space-y-3">
                <label class="block font-title-md text-body-md font-semibold text-on-surface" for="field-specialty">
                  Especialidad Médica <span class="text-tertiary">*</span>
                </label>
                <div class="relative">
                  <select
                    class="w-full appearance-none bg-surface-container-low text-on-surface font-body-md text-body-md py-3.5 px-4 rounded-xl focus-primary transition-colors pr-10 border <?= isset($errors['especialidad']) ? 'border-error' : 'border-outline-variant' ?>"
                    id="field-specialty"
                    name="especialidad"
                    required
                  >
                    <option disabled value="" <?= $selectedSpecialtySlug === '' ? 'selected' : '' ?>>
                      Selecciona una especialidad médica...
                    </option>
                    <?php foreach ($specialties as $spec): ?>
                      <?php $isSpecSelected = ($selectedSpecialtySlug !== '' && strcasecmp($selectedSpecialtySlug, (string) $spec['id']) === 0); ?>
                      <option value="<?= e((string) $spec['id']) ?>" <?= $isSpecSelected ? 'selected' : '' ?>>
                        <?= e((string) $spec['name']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                  <span class="material-symbols-outlined text-[20px] text-outline absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none">
                    expand_more
                  </span>
                </div>
                <p id="error-specialty" class="font-label-sm text-label-sm text-error mt-1 <?= isset($errors['especialidad']) ? '' : 'hidden' ?>">
                  <?= e($errors['especialidad'] ?? '') ?>
                </p>
              </div>

              <!-- Doctor Selection (filtrado dinámico según especialidad elegida o sin preferencia) -->
              <div class="space-y-3">
                <label class="block font-title-md text-body-md font-semibold text-on-surface" for="field-doctor">
                  Médico especialista preferido
                  <span class="text-outline text-label-sm font-normal">(Opcional — o sin preferencia)</span>
                </label>
                <div class="relative">
                  <select
                    class="w-full appearance-none bg-surface-container-low text-on-surface font-body-md text-body-md py-3.5 px-4 rounded-xl focus-primary transition-colors pr-10 border <?= isset($errors['medico']) ? 'border-error' : 'border-outline-variant' ?>"
                    id="field-doctor"
                    name="medico"
                  >
                    <option value="" <?= $selectedDoctorSlug === '' ? 'selected' : '' ?>>
                      Cualquier especialista disponible (Sin preferencia)
                    </option>
                    <?php foreach ($bookingDoctors as $doc): ?>
                      <?php $isDocSelected = ($selectedDoctorSlug !== '' && strcasecmp($selectedDoctorSlug, (string) $doc['id']) === 0); ?>
                      <option
                        value="<?= e((string) $doc['id']) ?>"
                        data-specialties="<?= e(implode(' ', $doc['specialty_slugs'])) ?>"
                        <?= $isDocSelected ? 'selected' : '' ?>
                      >
                        <?= e((string) $doc['name']) ?> — <?= e((string) $doc['specialty']) ?><?= empty($doc['accredited']) ? ' (Por confirmar)' : '' ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                  <span class="material-symbols-outlined text-[20px] text-outline absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none">
                    person_search
                  </span>
                </div>
                <span id="doctor-filter-hint" class="font-label-sm text-label-sm text-outline block">
                  Selecciona una especialidad para ver sus médicos asignados, o mantén «Sin preferencia».
                </span>
                <p id="error-doctor" class="font-label-sm text-label-sm text-error mt-1 <?= isset($errors['medico']) ? '' : 'hidden' ?>">
                  <?= e($errors['medico'] ?? '') ?>
                </p>
              </div>

              <!-- Modality Pill -->
              <div class="space-y-3">
                <label class="block font-title-md text-body-md font-semibold text-on-surface">
                  Modalidad de atención
                </label>
                <div class="p-4 rounded-xl bg-surface-container-low flex items-center justify-between border border-outline-variant">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-primary-container text-on-primary flex items-center justify-center shrink-0">
                      <span class="material-symbols-outlined text-[20px]">apartment</span>
                    </div>
                    <div>
                      <span class="font-title-md text-title-md text-on-surface font-semibold block">
                        Consulta Presencial en Sede Pasaje
                      </span>
                      <span class="font-body-sm text-body-sm text-on-surface-variant">
                        Av. Quito y Sucre, Pasaje, El Oro
                      </span>
                    </div>
                  </div>
                  <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-secondary-container text-on-secondary-container font-label-sm text-label-sm font-bold">
                    <span class="material-symbols-outlined text-[14px]">check_circle</span>
                    Instalación oficial
                  </span>
                </div>
              </div>

              <!-- Navigation Action -->
              <div class="pt-6 flex justify-end">
                <button
                  class="inline-flex items-center gap-2 bg-primary-container text-on-primary font-title-md text-label-md font-semibold px-8 py-3.5 rounded-full hover:bg-primary transition-all shadow-sm hover:scale-[1.01]"
                  data-goto-step="2"
                  type="button"
                >
                  <span>Continuar al Paso 2</span>
                  <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                </button>
              </div>
            </div>

            <!-- STEP 2: FECHA Y TURNO -->
            <div data-step-panel="2" class="hidden space-y-8 animate-fadeIn">
              <div class="border-b border-surface-container-high pb-4">
                <div class="flex items-center gap-2 text-primary font-label-md text-label-md font-bold uppercase tracking-wider mb-1">
                  <span class="material-symbols-outlined text-[18px]">calendar_month</span>
                  <span>Disponibilidad</span>
                </div>
                <h2 class="font-headline-sm text-headline-sm text-on-surface font-semibold">
                  Elegir fecha estimada y turno
                </h2>
                <p class="font-body-sm text-body-sm text-on-surface-variant mt-1">
                  Selecciona tus preferencias horarias. Recuerda que el cupo exacto se valida con Admisión.
                </p>
              </div>

              <!-- Date Picker with notice -->
              <div class="space-y-3">
                <label class="block font-title-md text-body-md font-semibold text-on-surface" for="field-date">
                  Fecha preferida de consulta <span class="text-tertiary">*</span>
                </label>
                <div class="relative">
                  <input
                    class="w-full bg-surface-container-low text-on-surface font-body-md text-body-md py-3.5 px-4 rounded-xl focus-primary transition-colors border <?= isset($errors['fecha']) ? 'border-error' : 'border-outline-variant' ?>"
                    id="field-date"
                    name="fecha"
                    min="<?= e($todayDate) ?>"
                    max="<?= e($maxDate) ?>"
                    type="date"
                    value="<?= e($dateValue) ?>"
                    required
                  />
                </div>
                <p id="error-date" class="font-label-sm text-label-sm text-error mt-1 <?= isset($errors['fecha']) ? '' : 'hidden' ?>">
                  <?= e($errors['fecha'] ?? '') ?>
                </p>
                <div class="p-3.5 rounded-xl bg-surface-container-high flex items-start gap-3 border border-outline-variant">
                  <span class="material-symbols-outlined text-primary text-[20px] shrink-0 mt-0.5">
                    info
                  </span>
                  <p class="font-body-sm text-body-sm text-on-surface leading-snug">
                    <strong>Nota de asignación:</strong> La fecha seleccionada representa tu preferencia. Nuestro equipo de admisión verificará la agenda clínica del especialista y confirmará tu turno definitivo vía telefónica o WhatsApp.
                  </p>
                </div>
              </div>

              <!-- Shift Selection (Mañana / Tarde) -->
              <div class="space-y-3">
                <label class="block font-title-md text-body-md font-semibold text-on-surface">
                  Franja horaria de conveniencia <span class="text-tertiary">*</span>
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <label
                    data-shift-card="manana"
                    class="cursor-pointer relative p-4 rounded-xl transition-all flex items-center justify-between border <?= !$isEvening ? 'bg-surface-container-highest border-primary' : 'bg-surface-container-low border-outline-variant hover:bg-surface-container' ?>"
                  >
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 rounded-full bg-surface-container text-primary flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[20px]">light_mode</span>
                      </div>
                      <div>
                        <span class="font-title-md text-title-md text-on-surface font-semibold block">
                          Turno Mañana
                        </span>
                        <span class="font-body-sm text-body-sm text-on-surface-variant">
                          08:00 — 12:00
                        </span>
                      </div>
                    </div>
                    <input
                      type="radio"
                      name="shift-preference"
                      value="Mañana (08:00 - 12:00)"
                      class="w-5 h-5 accent-primary"
                      <?= !$isEvening ? 'checked' : '' ?>
                    />
                  </label>
                  <label
                    data-shift-card="tarde"
                    class="cursor-pointer relative p-4 rounded-xl transition-all flex items-center justify-between border <?= $isEvening ? 'bg-surface-container-highest border-primary' : 'bg-surface-container-low border-outline-variant hover:bg-surface-container' ?>"
                  >
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 rounded-full bg-surface-container text-primary flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[20px]">wb_twilight</span>
                      </div>
                      <div>
                        <span class="font-title-md text-title-md text-on-surface font-semibold block">
                          Turno Tarde
                        </span>
                        <span class="font-body-sm text-body-sm text-on-surface-variant">
                          14:00 — 18:00
                        </span>
                      </div>
                    </div>
                    <input
                      type="radio"
                      name="shift-preference"
                      value="Tarde (14:00 - 18:00)"
                      class="w-5 h-5 accent-primary"
                      <?= $isEvening ? 'checked' : '' ?>
                    />
                  </label>
                </div>
                <p id="error-shift" class="font-label-sm text-label-sm text-error mt-1 <?= isset($errors['turno']) ? '' : 'hidden' ?>">
                  <?= e($errors['turno'] ?? '') ?>
                </p>
              </div>

              <!-- Navigation Actions -->
              <div class="pt-6 flex items-center justify-between">
                <button
                  class="inline-flex items-center gap-2 text-on-surface-variant hover:text-primary font-title-md text-label-md font-semibold px-4 py-3 rounded-full transition-colors"
                  data-goto-step="1"
                  type="button"
                >
                  <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                  <span>Regresar</span>
                </button>
                <button
                  class="inline-flex items-center gap-2 bg-primary-container text-on-primary font-title-md text-label-md font-semibold px-8 py-3.5 rounded-full hover:bg-primary transition-all shadow-sm"
                  data-goto-step="3"
                  type="button"
                >
                  <span>Continuar al Paso 3</span>
                  <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                </button>
              </div>
            </div>

            <!-- STEP 3: DATOS DEL PACIENTE -->
            <div data-step-panel="3" class="hidden space-y-8 animate-fadeIn">
              <div class="border-b border-surface-container-high pb-4">
                <div class="flex items-center gap-2 text-primary font-label-md text-label-md font-bold uppercase tracking-wider mb-1">
                  <span class="material-symbols-outlined text-[18px]">badge</span>
                  <span>Información de Contacto</span>
                </div>
                <h2 class="font-headline-sm text-headline-sm text-on-surface font-semibold">
                  Datos del paciente
                </h2>
                <p class="font-body-sm text-body-sm text-on-surface-variant mt-1">
                  Ingresa los datos esenciales para comunicarnos y validar tu registro institucional.
                </p>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="space-y-2">
                  <label class="block font-title-md text-body-md font-semibold text-on-surface" for="field-fullname">
                    Nombre y Apellidos completos <span class="text-tertiary">*</span>
                  </label>
                  <input
                    class="w-full bg-surface-container-low text-on-surface font-body-md text-body-md py-3.5 px-4 rounded-xl focus-primary transition-colors border <?= isset($errors['paciente_nombre']) ? 'border-error' : 'border-outline-variant' ?>"
                    id="field-fullname"
                    name="paciente_nombre"
                    placeholder="Ej. Mariana Valarezo Gómez"
                    type="text"
                    value="<?= e($fullnameValue) ?>"
                    required
                  />
                  <p id="error-fullname" class="font-label-sm text-label-sm text-error mt-1 <?= isset($errors['paciente_nombre']) ? '' : 'hidden' ?>">
                    <?= e($errors['paciente_nombre'] ?? '') ?>
                  </p>
                </div>

                <div class="space-y-2">
                  <label class="block font-title-md text-body-md font-semibold text-on-surface" for="field-document">
                    Cédula o Pasaporte <span class="text-tertiary">*</span>
                  </label>
                  <div class="relative">
                    <input
                      class="w-full bg-surface-container-low text-on-surface font-body-md text-body-md py-3.5 px-4 rounded-xl focus-primary transition-colors pl-12 border <?= isset($errors['paciente_documento']) ? 'border-error' : 'border-outline-variant' ?>"
                      id="field-document"
                      name="paciente_documento"
                      placeholder="Ej. 0701234567 o AB123456"
                      type="text"
                      maxlength="20"
                      value="<?= e($documentValue) ?>"
                      required
                    />
                    <span class="material-symbols-outlined text-[20px] text-outline absolute left-4 top-1/2 -translate-y-1/2">
                      badge
                    </span>
                  </div>
                  <p id="error-document" class="font-label-sm text-label-sm text-error mt-1 <?= isset($errors['paciente_documento']) ? '' : 'hidden' ?>">
                    <?= e($errors['paciente_documento'] ?? '') ?>
                  </p>
                </div>

                <div class="space-y-2">
                  <label class="block font-title-md text-body-md font-semibold text-on-surface" for="field-phone">
                    Teléfono móvil o WhatsApp <span class="text-tertiary">*</span>
                  </label>
                  <div class="relative">
                    <input
                      class="w-full bg-surface-container-low text-on-surface font-body-md text-body-md py-3.5 px-4 rounded-xl focus-primary transition-colors pl-12 border <?= isset($errors['paciente_telefono']) ? 'border-error' : 'border-outline-variant' ?>"
                      id="field-phone"
                      name="paciente_telefono"
                      placeholder="098 765 4321"
                      type="tel"
                      value="<?= e($phoneValue) ?>"
                      required
                    />
                    <span class="material-symbols-outlined text-[20px] text-outline absolute left-4 top-1/2 -translate-y-1/2">
                      phone_iphone
                    </span>
                  </div>
                  <span class="font-label-sm text-label-sm text-outline block">
                    Requerido para coordinar y notificar tu cita.
                  </span>
                  <p id="error-phone" class="font-label-sm text-label-sm text-error mt-1 <?= isset($errors['paciente_telefono']) ? '' : 'hidden' ?>">
                    <?= e($errors['paciente_telefono'] ?? '') ?>
                  </p>
                </div>

                <div class="space-y-2">
                  <label class="block font-title-md text-body-md font-semibold text-on-surface" for="field-email">
                    Correo electrónico
                    <span class="text-outline text-label-sm font-normal">(Opcional)</span>
                  </label>
                  <div class="relative">
                    <input
                      class="w-full bg-surface-container-low text-on-surface font-body-md text-body-md py-3.5 px-4 rounded-xl focus-primary transition-colors pl-12 border <?= isset($errors['paciente_email']) ? 'border-error' : 'border-outline-variant' ?>"
                      id="field-email"
                      name="paciente_email"
                      placeholder="mariana.valarezo@ejemplo.com"
                      type="email"
                      value="<?= e($emailValue) ?>"
                    />
                    <span class="material-symbols-outlined text-[20px] text-outline absolute left-4 top-1/2 -translate-y-1/2">
                      mail
                    </span>
                  </div>
                  <p id="error-email" class="font-label-sm text-label-sm text-error mt-1 <?= isset($errors['paciente_email']) ? '' : 'hidden' ?>">
                    <?= e($errors['paciente_email'] ?? '') ?>
                  </p>
                </div>

                <div class="sm:col-span-2 space-y-2">
                  <label class="block font-title-md text-body-md font-semibold text-on-surface" for="field-comments">
                    Motivo de consulta o comentarios
                    <span class="text-outline text-label-sm font-normal">(Opcional)</span>
                  </label>
                  <textarea
                    class="w-full bg-surface-container-low text-on-surface font-body-md text-body-md py-3 px-4 rounded-xl focus-primary transition-colors resize-none border <?= isset($errors['motivo']) ? 'border-error' : 'border-outline-variant' ?>"
                    id="field-comments"
                    name="motivo"
                    maxlength="500"
                    placeholder="Ej. Control preventivo, valoración inicial, asistencia con silla de ruedas en el acceso, etc. (No incluir antecedentes clínicos sensibles)."
                    rows="3"
                  ><?= e($reasonValue) ?></textarea>
                  <p id="error-comments" class="font-label-sm text-label-sm text-error mt-1 <?= isset($errors['motivo']) ? '' : 'hidden' ?>">
                    <?= e($errors['motivo'] ?? '') ?>
                  </p>
                </div>
              </div>

              <div class="p-4 rounded-xl bg-surface-container-low border <?= isset($errors['privacidad']) ? 'border-error' : 'border-outline-variant' ?>">
                <label class="flex items-start gap-3 cursor-pointer">
                  <input
                    id="field-privacy"
                    name="privacidad"
                    value="1"
                    type="checkbox"
                    <?= $privacyChecked ? 'checked' : '' ?>
                    class="w-5 h-5 mt-0.5 accent-primary rounded"
                    required
                  />
                  <span class="font-body-sm text-body-sm text-on-surface leading-snug">
                    He leído y acepto la <strong>política de protección de datos personales</strong> del Hospital San Marcos. Entiendo que mis datos se utilizarán estrictamente para la gestión administrativa de mi cita médica en Pasaje, El Oro. <span class="text-tertiary">*</span>
                  </span>
                </label>
                <p id="error-privacy" class="font-label-sm text-label-sm text-error mt-2 <?= isset($errors['privacidad']) ? '' : 'hidden' ?>">
                  <?= e($errors['privacidad'] ?? '') ?>
                </p>
              </div>

              <div class="pt-6 flex items-center justify-between">
                <button
                  class="inline-flex items-center gap-2 text-on-surface-variant hover:text-primary font-title-md text-label-md font-semibold px-4 py-3 rounded-full transition-colors"
                  data-goto-step="2"
                  type="button"
                >
                  <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                  <span>Regresar</span>
                </button>
                <button
                  class="inline-flex items-center gap-2 bg-primary-container text-on-primary font-title-md text-label-md font-semibold px-8 py-3.5 rounded-full hover:bg-primary transition-all shadow-sm"
                  data-goto-step="4"
                  type="button"
                >
                  <span>Revisar Resumen</span>
                  <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                </button>
              </div>
            </div>

            <!-- STEP 4: REVISAR Y CONFIRMAR -->
            <div data-step-panel="4" class="hidden space-y-8 animate-fadeIn">
              <div class="border-b border-surface-container-high pb-4">
                <div class="flex items-center gap-2 text-primary font-label-md text-label-md font-bold uppercase tracking-wider mb-1">
                  <span class="material-symbols-outlined text-[18px]">verified</span>
                  <span>Verificación Final</span>
                </div>
                <h2 class="font-headline-sm text-headline-sm text-on-surface font-semibold">
                  Resumen de tu solicitud
                </h2>
                <p class="font-body-sm text-body-sm text-on-surface-variant mt-1">
                  Revisa que todos tus datos estén correctos antes de registrar tu solicitud con Admisión.
                </p>
              </div>

              <!-- Summary Overview Cards -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-5 rounded-2xl bg-surface-container-low space-y-2 border border-outline-variant">
                  <span class="font-label-sm text-label-sm text-outline uppercase font-bold block">
                    1. Servicio Seleccionado
                  </span>
                  <p id="summary-specialty" class="font-title-md text-title-md font-bold text-on-surface">
                    <?= e($selectedSpecialtyLabel) ?>
                  </p>
                  <p id="summary-doctor" class="font-body-sm text-body-sm text-on-surface-variant">
                    Médico: <?= e($selectedDoctorLabel) ?>
                  </p>
                  <div class="inline-flex items-center gap-1.5 font-label-sm text-label-sm text-primary pt-1">
                    <span class="material-symbols-outlined text-[16px]">location_on</span>
                    <span>Sede Principal • Pasaje, El Oro</span>
                  </div>
                </div>

                <div class="p-5 rounded-2xl bg-surface-container-low space-y-2 border border-outline-variant">
                  <span class="font-label-sm text-label-sm text-outline uppercase font-bold block">
                    2. Horario Preferido
                  </span>
                  <p id="summary-date" class="font-title-md text-title-md font-bold text-on-surface">
                    <?= e($dateValue) ?>
                  </p>
                  <p id="summary-shift" class="font-body-sm text-body-sm text-on-surface-variant">
                    <?= e($shiftValue) ?>
                  </p>
                  <div class="inline-flex items-center gap-1.5 font-label-sm text-label-sm text-outline pt-1">
                    <span class="material-symbols-outlined text-[16px]">pending_actions</span>
                    <span>Sujeto a confirmación de turno por Admisión</span>
                  </div>
                </div>

                <div class="md:col-span-2 p-5 rounded-2xl bg-surface-container-low space-y-2 border border-outline-variant">
                  <span class="font-label-sm text-label-sm text-outline uppercase font-bold block">
                    3. Paciente Registrado
                  </span>
                  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div class="space-y-1">
                      <p id="summary-fullname" class="font-title-md text-title-md font-bold text-on-surface">
                        <?= e($fullnameValue !== '' ? $fullnameValue : 'Pendiente de ingresar') ?>
                      </p>
                      <p id="summary-document" class="font-body-sm text-body-sm text-on-surface-variant">
                        Documento: <?= e($documentValue !== '' ? $documentValue : 'Pendiente de ingresar') ?>
                      </p>
                      <p id="summary-phone" class="font-body-sm text-body-sm text-on-surface-variant">
                        Teléfono / WhatsApp: <?= e($phoneValue !== '' ? $phoneValue : 'Pendiente de ingresar') ?>
                      </p>
                    </div>
                    <div id="summary-email" class="font-body-sm text-body-sm text-outline">
                      <?= e($emailValue !== '' ? 'Correo: ' . $emailValue : 'Sin correo electrónico indicado') ?>
                    </div>
                  </div>
                  <p id="summary-reason" class="font-body-sm text-body-sm text-on-surface-variant pt-1 <?= $reasonValue !== '' ? '' : 'hidden' ?>">
                    <?= e($reasonValue !== '' ? 'Motivo / Comentarios: ' . $reasonValue : '') ?>
                  </p>
                </div>
              </div>

              <!-- Aviso institucional de proceso de confirmación -->
              <div class="p-6 rounded-2xl bg-surface-container-high/80 border-2 border-primary/30 space-y-4">
                <div class="flex items-start gap-3">
                  <span class="material-symbols-outlined text-primary text-[28px] shrink-0 mt-0.5">
                    info
                  </span>
                  <div class="space-y-1">
                    <h3 class="font-title-md text-body-md font-bold text-on-surface">
                      Confirmación de Turno por Admisión
                    </h3>
                    <p class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed">
                      Al enviar tu solicitud generaremos tu código único <strong>HCSM-XXXX</strong>. Nuestro equipo de Admisión revisará tu requerimiento y te contactará por teléfono o WhatsApp para confirmar la hora exacta de tu consulta.
                    </p>
                  </div>
                </div>

                <div class="flex flex-wrap items-center gap-3 pt-2">
                  <a
                    href="tel:0987973511"
                    class="inline-flex items-center justify-center gap-2 bg-surface-container-lowest text-primary font-title-md text-label-md font-bold px-5 py-2.5 rounded-full hover:bg-surface-container transition-all shadow-sm border border-outline-variant"
                  >
                    <span class="material-symbols-outlined text-[18px]">phone_forwarded</span>
                    <span>Central: 098 797 3511</span>
                  </a>
                  <a
                    id="summary-whatsapp-link"
                    href="https://wa.me/593987973511"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center justify-center gap-2 bg-secondary text-on-secondary font-title-md text-label-md font-semibold px-5 py-2.5 rounded-full hover:scale-105 transition-all shadow-sm"
                  >
                    <span class="material-symbols-outlined text-[18px]">chat</span>
                    <span>WhatsApp Oficial de Admisión</span>
                  </a>
                </div>
              </div>

              <!-- Navigation Actions -->
              <div class="pt-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                <button
                  class="w-full sm:w-auto inline-flex items-center justify-center gap-2 text-on-surface-variant hover:text-primary font-title-md text-label-md font-semibold px-4 py-3 rounded-full transition-colors"
                  data-goto-step="3"
                  type="button"
                >
                  <span class="material-symbols-outlined text-[18px]">edit</span>
                  <span>Modificar datos</span>
                </button>
                <button
                  id="appointment-submit-btn"
                  class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-primary text-on-primary hover:bg-on-primary-container transition-all shadow-sm font-title-md text-body-md font-bold px-8 py-3.5 rounded-full cursor-pointer"
                  type="submit"
                >
                  <span class="material-symbols-outlined text-[20px]">check_circle</span>
                  <span id="appointment-submit-label">Confirmar solicitud de cita</span>
                </button>
              </div>
            </div>
            </form>
          </div>
          <?php endif; ?>

          <!-- Privacy & Security Guarantee Banner -->
          <div class="p-6 rounded-2xl bg-surface-container-low flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border border-outline-variant">
            <div class="flex items-start gap-4">
              <div class="w-10 h-10 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0 mt-0.5">
                <span class="material-symbols-outlined text-[22px]">lock</span>
              </div>
              <div>
                <h4 class="font-title-md text-title-md font-bold text-on-surface">
                  Compromiso de Privacidad y Soporte Directo
                </h4>
                <p class="font-body-sm text-body-sm text-on-surface-variant max-w-xl">
                  Este formulario solicita únicamente datos de contacto para coordinación administrativa. En cumplimiento de normativas de salud, <strong>no se solicitan historiales clínicos ni diagnósticos reservados</strong> por este canal web.
                </p>
              </div>
            </div>
            <a
              class="inline-flex items-center gap-2 px-4 py-2.5 rounded-full bg-surface-container-highest text-primary hover:bg-primary-container hover:text-on-primary transition-colors font-title-md text-label-md font-semibold whitespace-nowrap self-stretch sm:self-auto justify-center border border-outline-variant"
              href="tel:0987973511"
            >
              <span class="material-symbols-outlined text-[18px]">call</span>
              <span>098 797 3511</span>
            </a>
          </div>
        </div>

        <!-- Right: Asistente Virtual San Marcos & Hospital Guidance (4 cols) -->
        <div class="lg:col-span-4 space-y-6">
          <!-- Virtual Assistant Institutional Widget -->
          <div class="bg-surface-container-lowest p-6 rounded-2xl shadow-sm space-y-5 border border-outline-variant">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-primary-container text-on-primary flex items-center justify-center shrink-0">
                  <span class="material-symbols-outlined text-[22px]">smart_toy</span>
                </div>
                <div>
                  <h3 class="font-title-md text-title-md font-bold text-on-surface">
                    Asistente San Marcos
                  </h3>
                  <span class="font-label-sm text-label-sm text-secondary font-semibold flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-secondary inline-block"></span>
                    Orientación en línea
                  </span>
                </div>
              </div>
              <span class="text-outline text-label-sm font-semibold uppercase">Pasaje</span>
            </div>
            <p class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed">
              ¿Necesitas ayuda rápida con tu cita o encontrar una especialidad médica adecuada para tus síntomas?
            </p>

            <!-- Interactive Quick Options -->
            <div class="space-y-2.5">
              <button
                class="w-full text-left p-3.5 rounded-xl bg-surface-container-low hover:bg-surface-container transition-all flex items-center justify-between group border border-outline-variant"
                data-assistant-topic="especialidades"
                type="button"
              >
                <div class="flex items-center gap-2.5">
                  <span class="material-symbols-outlined text-[18px] text-primary">stethoscope</span>
                  <span class="font-title-md text-body-sm font-medium text-on-surface">
                    Encontrar especialidades médicas
                  </span>
                </div>
                <span class="material-symbols-outlined text-[16px] text-outline group-hover:translate-x-0.5 transition-transform">
                  chevron_right
                </span>
              </button>
              <button
                class="w-full text-left p-3.5 rounded-xl bg-surface-container-low hover:bg-surface-container transition-all flex items-center justify-between group border border-outline-variant"
                data-assistant-topic="laboratorio"
                type="button"
              >
                <div class="flex items-center gap-2.5">
                  <span class="material-symbols-outlined text-[18px] text-primary">science</span>
                  <span class="font-title-md text-body-sm font-medium text-on-surface">
                    Requisitos para exámenes de laboratorio
                  </span>
                </div>
                <span class="material-symbols-outlined text-[16px] text-outline group-hover:translate-x-0.5 transition-transform">
                  chevron_right
                </span>
              </button>
              <button
                class="w-full text-left p-3.5 rounded-xl bg-surface-container-low hover:bg-surface-container transition-all flex items-center justify-between group border border-outline-variant"
                data-assistant-topic="paso-a-paso"
                type="button"
              >
                <div class="flex items-center gap-2.5">
                  <span class="material-symbols-outlined text-[18px] text-primary">help_outline</span>
                  <span class="font-title-md text-body-sm font-medium text-on-surface">
                    Cómo solicitar una cita paso a paso
                  </span>
                </div>
                <span class="material-symbols-outlined text-[16px] text-outline group-hover:translate-x-0.5 transition-transform">
                  chevron_right
                </span>
              </button>
              <a
                class="w-full p-3.5 rounded-xl bg-secondary/10 hover:bg-secondary/20 transition-all flex items-center justify-between text-secondary border border-secondary/20"
                href="https://wa.me/593987973511"
                rel="noopener noreferrer"
                target="_blank"
              >
                <div class="flex items-center gap-2.5">
                  <span class="material-symbols-outlined text-[18px]">chat</span>
                  <span class="font-title-md text-body-sm font-bold">
                    Hablar directamente por WhatsApp
                  </span>
                </div>
                <span class="material-symbols-outlined text-[16px]">open_in_new</span>
              </a>
            </div>

            <!-- Aviso visible de disponibilidad próxima del Asistente San Marcos -->
            <div
              id="assistant-output-box"
              class="p-4 rounded-xl bg-surface-container-high transition-all space-y-2 border border-outline-variant animate-fadeIn"
            >
              <div class="flex items-center justify-between">
                <span id="assistant-output-title" class="font-label-sm text-label-sm font-bold text-primary uppercase flex items-center gap-1.5">
                  <span class="material-symbols-outlined text-[16px]">info</span>
                  <span>Función en Integración</span>
                </span>
              </div>
              <p id="assistant-output-text" class="font-body-sm text-body-sm text-on-surface leading-snug">
                El asistente virtual interactivo del Hospital San Marcos estará disponible próximamente. Para recibir orientación inmediata sobre especialidades, requisitos o citas, comuníquese mediante WhatsApp o al 098 797 3511.
              </p>
            </div>

            <!-- Ethical Disclaimer -->
            <div class="pt-4 border-t border-surface-container-high flex items-start gap-2.5 text-outline">
              <span class="material-symbols-outlined text-[18px] shrink-0 mt-0.5">gavel</span>
              <p class="font-body-sm text-label-sm leading-tight">
                <strong>Advertencia ética:</strong> Este asistente brinda exclusivamente orientación administrativa y de navegación. <strong>No emite diagnósticos médicos ni sustituye la consulta médica presencial o de urgencia.</strong>
              </p>
            </div>
          </div>

          <!-- Regional Hospital Service Card -->
          <div class="bg-surface-container-lowest p-6 rounded-2xl shadow-sm space-y-4 border border-outline-variant">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-full bg-surface-container text-primary flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[20px]">access_time</span>
              </div>
              <div>
                <h4 class="font-title-md text-body-md font-bold text-on-surface">
                  Horarios de Admisión
                </h4>
                <span class="font-body-sm text-body-sm text-on-surface-variant">
                  Sede Pasaje • El Oro
                </span>
              </div>
            </div>
            <div class="space-y-2 font-body-sm text-body-sm">
              <div class="flex justify-between py-1 border-b border-surface-container-high">
                <span class="text-on-surface-variant">Consulta Externa</span>
                <span class="font-semibold text-on-surface">Lun - Sáb: 07:30 - 18:30</span>
              </div>
              <div class="flex justify-between py-1 border-b border-surface-container-high">
                <span class="text-on-surface-variant">Laboratorio Clínico</span>
                <span class="font-semibold text-on-surface">Lun - Dom: 06:30 - 19:00</span>
              </div>
              <div class="flex justify-between py-1">
                <span class="text-on-surface-variant">Área de Emergencias</span>
                <span class="font-bold text-error">24 Horas / 365 Días</span>
              </div>
            </div>
            <div class="pt-2">
              <div class="p-3 rounded-xl bg-surface-container-low flex items-center gap-3 border border-outline-variant">
                <span class="material-symbols-outlined text-primary text-[20px]">pin_drop</span>
                <span class="font-body-sm text-body-sm text-on-surface">
                  Av. Quito y Sucre, Pasaje (Frente al Parque Central)
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>
