<?php
declare(strict_types=1);

if (!defined('HSM_APP')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

$currentPage      = 'Contacto';
$confirmedContact = Security::pullFlash(ContactController::FLASH_KEY);
$errors           = is_array($formFeedback['errors'] ?? null) ? $formFeedback['errors'] : [];
$old              = is_array($formFeedback['old'] ?? null) ? $formFeedback['old'] : [];

$nombreVal   = (string) ($old['nombre'] ?? '');
$emailVal    = (string) ($old['email'] ?? '');
$telefonoVal = (string) ($old['telefono'] ?? '');
$asuntoVal   = (string) ($old['asunto'] ?? '');
$mensajeVal  = (string) ($old['mensaje'] ?? '');
$datosCheck  = ($old['datos'] ?? '') === '1';
?>
<div class="hospital-page-hero flex flex-col w-full">
  <!-- Decorative Top Ambient Header -->
  <div class="relative w-full overflow-hidden pb-12 pt-8 bg-surface-container-low">
    <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-[720px] h-[360px] bg-gradient-to-b from-primary-container/15 via-surface-container-high/20 to-transparent blur-3xl pointer-events-none"></div>
    <div class="max-w-7xl mx-auto px-gutter relative z-10">
      <!-- Breadcrumb -->
      <?php require __DIR__ . '/../partials/breadcrumb.php'; ?>

      <!-- Editorial Page Heading -->
      <div class="hospital-hero-grid grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <div class="hospital-hero-copy lg:col-span-8 space-y-3">
          <h1 class="hospital-hero-title font-display text-display text-on-surface tracking-tight leading-tight">
            Estamos aquí para ayudarte.
          </h1>
          <p class="hospital-hero-description font-body-lg text-body-lg text-on-surface-variant max-w-3xl leading-relaxed">
            Comunícate con nuestro equipo de atención al paciente, ubica nuestras instalaciones en Pasaje o solicita asistencia inmediata ante emergencias.
          </p>
        </div>
        <div class="lg:col-span-4 flex lg:justify-end items-center gap-3">
          <div class="hospital-hero-card flex items-center gap-3 px-4 py-3 rounded-2xl bg-surface-container-lowest shadow-sm border border-outline-variant">
            <div class="w-10 h-10 rounded-xl bg-surface-container-low flex items-center justify-center text-primary">
              <span class="material-symbols-outlined text-[22px]">verified</span>
            </div>
            <div>
              <div class="font-label-sm text-label-sm text-outline uppercase tracking-wider">
                Respuesta media
              </div>
              <div class="font-title-md text-title-md font-semibold text-on-surface">
                &lt; 15 min en WhatsApp
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Quick Access Grid (4 modules) -->
  <section class="max-w-7xl mx-auto px-gutter w-full py-12">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
      <!-- Card 1: Llamar al hospital -->
      <a
        class="group relative flex flex-col justify-between p-6 rounded-2xl bg-surface-container-lowest shadow-sm hover:shadow-md transition-all duration-300 hover:-translate-y-0.5 border border-outline-variant"
        href="tel:0987973511"
      >
        <div class="space-y-4">
          <div class="flex items-center justify-between">
            <div class="w-12 h-12 rounded-2xl bg-surface-container-low text-primary flex items-center justify-center group-hover:bg-primary group-hover:text-on-primary transition-colors">
              <span class="material-symbols-outlined text-[24px]">call</span>
            </div>
            <span class="material-symbols-outlined text-outline text-[20px] group-hover:text-primary transition-colors">
              arrow_outward
            </span>
          </div>
          <div>
            <div class="font-label-sm text-label-sm text-outline uppercase tracking-wider mb-1">
              Central Telefónica
            </div>
            <div class="font-headline-sm text-headline-sm font-semibold text-on-surface leading-snug">
              098 797 3511
            </div>
            <p class="font-body-sm text-body-sm text-on-surface-variant mt-2 leading-relaxed">
              Atención telefónica, información de admisiones y orientación general.
            </p>
          </div>
        </div>
        <div class="pt-4 mt-4 flex items-center gap-1.5 font-label-md text-label-md text-primary font-semibold border-t border-outline-variant">
          <span>Marcar ahora</span>
          <span class="material-symbols-outlined text-[16px]">chevron_right</span>
        </div>
      </a>

      <!-- Card 2: WhatsApp Directo -->
      <a
        class="group relative flex flex-col justify-between p-6 rounded-2xl bg-surface-container-lowest shadow-sm hover:shadow-md transition-all duration-300 hover:-translate-y-0.5 border border-outline-variant"
        href="https://wa.me/593987973511"
        rel="noopener noreferrer"
        target="_blank"
      >
        <div class="space-y-4">
          <div class="flex items-center justify-between">
            <div class="w-12 h-12 rounded-2xl bg-secondary-fixed/50 text-secondary flex items-center justify-center group-hover:bg-secondary group-hover:text-on-secondary transition-colors">
              <span class="material-symbols-outlined text-[24px]">chat</span>
            </div>
            <span class="material-symbols-outlined text-outline text-[20px] group-hover:text-secondary transition-colors">
              arrow_outward
            </span>
          </div>
          <div>
            <div class="font-label-sm text-label-sm text-secondary uppercase tracking-wider mb-1 font-bold">
              Mensajería Activa
            </div>
            <div class="font-headline-sm text-headline-sm font-semibold text-on-surface leading-snug">
              Escribir por WhatsApp
            </div>
            <p class="font-body-sm text-body-sm text-on-surface-variant mt-2 leading-relaxed">
              Canal directo para asignación de turnos, consultas rápidas y resultados.
            </p>
          </div>
        </div>
        <div class="pt-4 mt-4 flex items-center gap-1.5 font-label-md text-label-md text-secondary font-semibold border-t border-outline-variant">
          <span>Abrir chat oficial</span>
          <span class="material-symbols-outlined text-[16px]">chevron_right</span>
        </div>
      </a>

      <!-- Card 3: Cómo llegar -->
      <div class="hospital-hero-card relative flex flex-col justify-between p-6 rounded-2xl bg-surface-container-lowest shadow-sm border border-outline-variant">
        <div class="space-y-4">
          <div class="flex items-center justify-between">
            <div class="w-12 h-12 rounded-2xl bg-surface-container-low text-primary flex items-center justify-center">
              <span class="material-symbols-outlined text-[24px]">location_on</span>
            </div>
            <span class="font-label-sm text-label-sm text-outline bg-surface-container-high px-2 py-0.5 rounded-full">
              Sede Matriz
            </span>
          </div>
          <div>
            <div class="font-label-sm text-label-sm text-outline uppercase tracking-wider mb-1">
              Ubicación Física
            </div>
            <div class="font-title-lg text-title-lg font-bold text-on-surface leading-snug">
              Av. Quito y Sucre
            </div>
            <p class="font-body-sm text-body-sm text-on-surface-variant mt-2 leading-relaxed">
              Pasaje, Provincia de El Oro, Ecuador. Fácil acceso vehicular y peatonal.
            </p>
          </div>
        </div>
        <div class="pt-4 mt-4 font-body-sm text-body-sm text-primary font-medium flex items-center gap-1 border-t border-outline-variant">
          <span class="material-symbols-outlined text-[18px]">directions_car</span>
          <span>Zona con parqueo contiguo</span>
        </div>
      </div>

      <!-- Card 4: Consultar emergencias (Badge Rojo) -->
      <div class="relative flex flex-col justify-between p-6 rounded-2xl bg-error-container text-on-error-container shadow-sm overflow-hidden border border-error-container">
        <div class="absolute -right-6 -bottom-6 w-28 h-28 bg-error/10 rounded-full pointer-events-none"></div>
        <div class="space-y-4 relative z-10">
          <div class="flex items-center justify-between">
            <div class="w-12 h-12 rounded-2xl bg-error text-on-error flex items-center justify-center shadow-sm">
              <span class="material-symbols-outlined text-[24px]">e911_emergency</span>
            </div>
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-error text-on-error font-label-sm text-label-sm uppercase font-bold tracking-wider">
              <span class="w-1.5 h-1.5 rounded-full bg-white animate-ping"></span>
              24H / 365 Días
            </span>
          </div>
          <div>
            <div class="font-label-sm text-label-sm text-on-error-container uppercase tracking-wider mb-1 font-bold">
              Unidad Crítica
            </div>
            <div class="font-headline-sm text-headline-sm font-semibold text-on-error-container leading-snug">
              Emergencias Médicas
            </div>
            <p class="font-body-sm text-body-sm text-on-error-container/90 mt-2 leading-relaxed">
              Atención médica y triaje permanente. Médicos de guardia y soporte vital continuo.
            </p>
          </div>
        </div>
        <a
          class="relative z-10 pt-4 mt-4 inline-flex items-center justify-between font-label-md text-label-md font-bold text-error hover:underline border-t border-error/20"
          href="tel:0987973511"
        >
          <span>Línea directa de triaje</span>
          <span class="material-symbols-outlined text-[18px]">phone_in_talk</span>
        </a>
      </div>
    </div>
  </section>

  <!-- Two Column Core Section: Form & Map/Channels -->
  <section class="max-w-7xl mx-auto px-gutter w-full pb-20">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
      <!-- Columna Izquierda: Formulario Oficial (7 cols) -->
      <div class="lg:col-span-7 bg-surface-container-lowest p-8 sm:p-10 rounded-3xl shadow-sm space-y-8 border border-outline-variant">
        <div>
          <div class="inline-flex items-center gap-2 font-label-sm text-label-sm uppercase tracking-wider text-outline mb-2">
            <span class="material-symbols-outlined text-[18px] text-primary">edit_note</span>
            Formulario Oficial de Contacto
          </div>
          <h2 class="font-headline-md text-headline-md font-semibold text-on-surface">
            Envíanos un mensaje
          </h2>
          <p class="font-body-md text-body-md text-on-surface-variant mt-1 leading-relaxed">
            Completa tus datos para consultas administrativas, información de admisiones o convenios. Nuestro equipo responderá a la brevedad posible.
          </p>
        </div>

        <?php if ($confirmedContact !== null): ?>
          <div id="contacto-confirmacion" role="status" class="space-y-6 animate-fadeIn">
            <div class="p-6 rounded-2xl bg-surface-container-low border border-primary space-y-3">
              <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-secondary-container text-on-secondary-container font-label-sm text-label-sm font-bold uppercase tracking-wider">
                <span class="material-symbols-outlined text-[16px]">check_circle</span>
                <span>Mensaje Recibido</span>
              </div>
              <h3 class="font-headline-sm text-headline-sm font-semibold text-on-surface">
                Gracias por escribirnos, <?= e((string) $confirmedContact['name']) ?>
              </h3>
              <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
                Tu consulta sobre <strong><?= e((string) $confirmedContact['subject_label']) ?></strong> quedó registrada con el código <strong class="text-primary tracking-widest"><?= e((string) $confirmedContact['code']) ?></strong>. Nuestro equipo te responderá al correo <strong><?= e((string) $confirmedContact['email']) ?></strong>.
              </p>
            </div>
            <div class="pt-2 flex flex-wrap items-center justify-between gap-4">
              <a
                href="<?= e(url('/contacto')) ?>"
                class="inline-flex items-center justify-center gap-2 bg-primary text-on-primary font-title-md text-label-md font-bold px-7 py-3.5 rounded-full hover:bg-on-primary-container transition-all shadow-sm"
              >
                <span class="material-symbols-outlined text-[18px]">edit_note</span>
                <span>Enviar otro mensaje</span>
              </a>
              <a
                href="<?= e(url('/')) ?>"
                class="inline-flex items-center gap-2 text-on-surface-variant hover:text-primary font-title-md text-label-md font-semibold px-4 py-3 rounded-full transition-colors"
              >
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                <span>Volver al inicio</span>
              </a>
            </div>
          </div>
        <?php else: ?>
        <form id="contact-form" method="post" action="<?= e(url('/contacto')) ?>" class="space-y-6" novalidate>
          <input type="hidden" name="_csrf" value="<?= e(Security::csrfToken()) ?>" />
          <div class="sr-only" aria-hidden="true">
            <label for="contact-website-url">No completar este campo</label>
            <input type="text" id="contact-website-url" name="website_url" value="" tabindex="-1" autocomplete="off" />
          </div>

          <?php if ($errors !== []): ?>
            <div id="contact-server-errors" role="alert" class="p-4 rounded-xl bg-error-container text-on-error-container border border-outline-variant space-y-2">
              <div class="flex items-center gap-2 font-title-md text-body-md font-bold">
                <span class="material-symbols-outlined text-[20px]">error</span>
                <span>Por favor corrige los siguientes campos:</span>
              </div>
              <ul class="space-y-1 font-body-sm text-body-sm pl-6">
                <?php foreach ($errors as $errMsg): ?>
                  <li>• <?= e($errMsg) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>

          <!-- Nombre Completo -->
          <div class="space-y-2">
            <label class="block font-title-md text-label-md text-on-surface font-semibold" for="nombre">
              Nombre completo <span class="text-tertiary">*</span>
            </label>
            <div class="relative">
              <input
                class="w-full bg-surface-container-low text-on-surface placeholder:text-outline px-4 py-3.5 pl-11 rounded-xl text-body-md font-body-md focus:bg-surface-container-lowest focus-primary focus:ring-2 focus:ring-primary-container transition-all border <?= isset($errors['nombre']) ? 'border-error' : 'border-outline-variant' ?>"
                id="nombre"
                name="nombre"
                placeholder="Ej. Dra. María Augusta Morales"
                required
                type="text"
                value="<?= e($nombreVal) ?>"
              />
              <span class="material-symbols-outlined absolute left-3.5 top-3.5 text-outline text-[20px]">
                person
              </span>
            </div>
            <p id="error-contact-nombre" class="font-label-sm text-label-sm text-error mt-1 <?= isset($errors['nombre']) ? '' : 'hidden' ?>">
              <?= e($errors['nombre'] ?? '') ?>
            </p>
          </div>

          <!-- Correo y Teléfono en dos columnas -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <!-- Correo electrónico -->
            <div class="space-y-2">
              <label class="block font-title-md text-label-md text-on-surface font-semibold" for="email">
                Correo electrónico <span class="text-tertiary">*</span>
              </label>
              <div class="relative">
                <input
                  class="w-full bg-surface-container-low text-on-surface placeholder:text-outline px-4 py-3.5 pl-11 rounded-xl text-body-md font-body-md focus:bg-surface-container-lowest focus-primary focus:ring-2 focus:ring-primary-container transition-all border <?= isset($errors['email']) ? 'border-error' : 'border-outline-variant' ?>"
                  id="email"
                  name="email"
                  placeholder="paciente@correo.com"
                  required
                  type="email"
                  value="<?= e($emailVal) ?>"
                />
                <span class="material-symbols-outlined absolute left-3.5 top-3.5 text-outline text-[20px]">
                  mail
                </span>
              </div>
              <p id="error-contact-email" class="font-label-sm text-label-sm text-error mt-1 <?= isset($errors['email']) ? '' : 'hidden' ?>">
                <?= e($errors['email'] ?? '') ?>
              </p>
            </div>

            <!-- Teléfono (opcional) -->
            <div class="space-y-2">
              <div class="flex items-center justify-between">
                <label class="block font-title-md text-label-md text-on-surface font-semibold" for="telefono">
                  Teléfono de contacto
                </label>
                <span class="font-label-sm text-label-sm text-outline">Opcional</span>
              </div>
              <div class="relative">
                <input
                  class="w-full bg-surface-container-low text-on-surface placeholder:text-outline px-4 py-3.5 pl-11 rounded-xl text-body-md font-body-md focus:bg-surface-container-lowest focus-primary focus:ring-2 focus:ring-primary-container transition-all border <?= isset($errors['telefono']) ? 'border-error' : 'border-outline-variant' ?>"
                  id="telefono"
                  name="telefono"
                  placeholder="Ej. 099 123 4567"
                  type="tel"
                  value="<?= e($telefonoVal) ?>"
                />
                <span class="material-symbols-outlined absolute left-3.5 top-3.5 text-outline text-[20px]">
                  smartphone
                </span>
              </div>
              <p id="error-contact-telefono" class="font-label-sm text-label-sm text-error mt-1 <?= isset($errors['telefono']) ? '' : 'hidden' ?>">
                <?= e($errors['telefono'] ?? '') ?>
              </p>
            </div>
          </div>

          <!-- Asunto (Desplegable) -->
          <div class="space-y-2">
            <label class="block font-title-md text-label-md text-on-surface font-semibold" for="asunto">
              Asunto de la consulta <span class="text-tertiary">*</span>
            </label>
            <div class="relative">
              <select
                class="w-full bg-surface-container-low text-on-surface px-4 py-3.5 pl-11 pr-10 rounded-xl text-body-md font-body-md appearance-none focus-primary focus:ring-2 focus:ring-primary-container transition-all cursor-pointer border <?= isset($errors['asunto']) ? 'border-error' : 'border-outline-variant' ?>"
                id="asunto"
                name="asunto"
                required
              >
                <option disabled value="" <?= $asuntoVal === '' ? 'selected' : '' ?>>
                  Selecciona una opción institucional...
                </option>
                <option value="consulta-general" <?= $asuntoVal === 'consulta-general' ? 'selected' : '' ?>>Consulta general y orientación</option>
                <option value="admision-citas" <?= $asuntoVal === 'admision-citas' ? 'selected' : '' ?>>Admisión y citas con especialistas</option>
                <option value="servicios" <?= $asuntoVal === 'servicios' ? 'selected' : '' ?>>Información de servicios de laboratorio y diagnóstico</option>
                <option value="convenios" <?= $asuntoVal === 'convenios' ? 'selected' : '' ?>>Convenios aseguradoras y corporativos</option>
                <option value="sugerencias" <?= $asuntoVal === 'sugerencias' ? 'selected' : '' ?>>Sugerencias o felicitaciones</option>
              </select>
              <span class="material-symbols-outlined absolute left-3.5 top-3.5 text-outline text-[20px]">
                subject
              </span>
              <span class="material-symbols-outlined absolute right-3.5 top-3.5 text-outline text-[20px] pointer-events-none">
                expand_more
              </span>
            </div>
            <p id="error-contact-asunto" class="font-label-sm text-label-sm text-error mt-1 <?= isset($errors['asunto']) ? '' : 'hidden' ?>">
              <?= e($errors['asunto'] ?? '') ?>
            </p>
          </div>

          <!-- Mensaje -->
          <div class="space-y-2">
            <label class="block font-title-md text-label-md text-on-surface font-semibold" for="mensaje">
              Mensaje detallado <span class="text-tertiary">*</span>
            </label>
            <div class="relative">
              <textarea
                class="w-full bg-surface-container-low text-on-surface placeholder:text-outline p-4 pl-11 rounded-xl text-body-md font-body-md focus-primary focus:ring-2 focus:ring-primary-container transition-all resize-none border <?= isset($errors['mensaje']) ? 'border-error' : 'border-outline-variant' ?>"
                id="mensaje"
                name="mensaje"
                maxlength="1500"
                placeholder="Describe brevemente tu requerimiento, especialidad médica solicitada o consulta administrativa..."
                required
                rows="4"
              ><?= e($mensajeVal) ?></textarea>
              <span class="material-symbols-outlined absolute left-3.5 top-4 text-outline text-[20px]">
                chat_bubble_outline
              </span>
            </div>
            <p id="error-contact-mensaje" class="font-label-sm text-label-sm text-error mt-1 <?= isset($errors['mensaje']) ? '' : 'hidden' ?>">
              <?= e($errors['mensaje'] ?? '') ?>
            </p>
          </div>

          <!-- Checkbox Protección de Datos -->
          <div class="p-4 rounded-xl bg-surface-container-low/60 border <?= isset($errors['datos']) ? 'border-error' : 'border-outline-variant' ?>">
            <div class="flex items-start gap-3">
              <input
                class="mt-1 w-4 h-4 rounded text-primary focus:ring-primary-container focus:ring-offset-0 cursor-pointer accent-primary"
                id="datos"
                name="datos"
                value="1"
                <?= $datosCheck ? 'checked' : '' ?>
                required
                type="checkbox"
              />
              <label class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed cursor-pointer" for="datos">
                Acepto el tratamiento de datos personales para la atención de esta solicitud, conforme a los protocolos confidenciales de gestión de información médica y de contacto de Hospital San Marcos. <span class="text-tertiary">*</span>
              </label>
            </div>
            <p id="error-contact-datos" class="font-label-sm text-label-sm text-error mt-2 <?= isset($errors['datos']) ? '' : 'hidden' ?>">
              <?= e($errors['datos'] ?? '') ?>
            </p>
          </div>

          <!-- Botón de Envío Final Activo -->
          <div class="pt-2 flex flex-col sm:flex-row items-center justify-between gap-4">
            <button
              id="contact-submit-btn"
              class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 bg-primary text-on-primary hover:bg-on-primary-container transition-all shadow-sm font-title-md text-title-md font-semibold px-8 py-3.5 rounded-full cursor-pointer"
              type="submit"
            >
              <span class="material-symbols-outlined text-[20px]">send</span>
              <span id="contact-submit-label">Enviar mensaje</span>
            </button>
            <div class="flex items-center gap-2 text-outline font-label-sm text-label-sm">
              <span class="material-symbols-outlined text-[16px] text-secondary">lock</span>
              <span>Envío cifrado y confidencial</span>
            </div>
          </div>

          <!-- Nota de Aclaración Administrativa -->
          <div class="pt-4 flex items-start gap-3 p-4 rounded-xl bg-surface-container-high/40 text-on-surface-variant font-body-sm text-body-sm border border-outline-variant">
            <span class="material-symbols-outlined text-[20px] text-primary shrink-0 mt-0.5">info</span>
            <p class="leading-relaxed">
              <strong class="font-semibold text-on-surface">Atención inmediata:</strong> Para emergencias médicas las 24 horas comuníquese directamente a nuestra central telefónica (<a href="tel:0987973511" class="font-semibold text-primary hover:underline">098 797 3511</a>) o por <a href="https://wa.me/593987973511" target="_blank" rel="noopener noreferrer" class="font-semibold text-secondary hover:underline">WhatsApp Directo</a>.
            </p>
          </div>
        </form>
        <?php endif; ?>
      </div>

      <!-- Columna Derecha: Mapa, Canales Digitales y Accesos (5 cols) -->
      <div class="lg:col-span-5 space-y-6">
        <!-- Mapa y Geolocalización -->
        <div class="bg-surface-container-lowest p-6 rounded-3xl shadow-sm space-y-5 border border-outline-variant">
          <div class="flex items-center justify-between">
            <div>
              <span class="font-label-sm text-label-sm uppercase tracking-wider text-outline block">
                Georreferenciación
              </span>
              <h3 class="font-title-lg text-title-lg font-bold text-on-surface">
                Ubicación de la Clínica
              </h3>
            </div>
            <a
              class="inline-flex items-center gap-1 font-label-md text-label-md text-primary font-semibold hover:underline"
              href="https://maps.app.goo.gl/wSFw1YrfLKJfjsxN6"
              rel="noopener noreferrer"
              target="_blank"
            >
              <span>Abrir en Google Maps</span>
              <span class="material-symbols-outlined text-[16px]">open_in_new</span>
            </a>
          </div>

          <!-- Contenedor de Mapa -->
          <a
            href="https://maps.app.goo.gl/wSFw1YrfLKJfjsxN6"
            rel="noopener noreferrer"
            target="_blank"
            aria-label="Abrir ubicación exacta del Hospital San Marcos en Google Maps"
            class="block relative w-full h-64 rounded-2xl overflow-hidden shadow-inner group border border-outline-variant"
          >
            <div
              class="w-full h-full bg-cover bg-center"
              style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuB7yrHzQdKgkASaSlaWbDYiROUglFASJv5oxlSzhBBBEnos9GG26KjxxpM_hTiMq59iG8WS-gZT_dneNhNtD_K01zDr8VwlFJicxUAuy0myfIzY_If59IHdtum0E9N7JmO0idGddMVyo49fMLzhj8xmJEfc5HFtU6EOWF85baDQ8PSW4aDwW1CZWG1UvonpTOYsxYcfDAx1InqMhBcDI58WAxv4SuyEjF21SBOV1nQkHJXj2xbdWrtz9g');"
            ></div>
            <div class="absolute inset-0 bg-gradient-to-t from-inverse-surface/80 via-transparent to-transparent pointer-events-none"></div>
            <!-- Pin Badge Flotante -->
            <div class="absolute bottom-3 left-3 right-3 p-3 rounded-xl bg-surface-container-lowest/95 backdrop-blur-sm shadow-md flex items-center justify-between gap-3 border border-outline-variant">
              <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-8 h-8 rounded-full bg-primary text-on-primary flex items-center justify-center shrink-0">
                  <span class="material-symbols-outlined text-[18px]">local_hospital</span>
                </div>
                <div class="truncate">
                  <div class="font-title-md text-label-md font-bold text-on-surface truncate">
                    Hospital San Marcos
                  </div>
                  <div class="font-body-sm text-label-sm text-outline truncate">
                    Av. Quito y Sucre • Pasaje
                  </div>
                </div>
              </div>
              <span class="material-symbols-outlined text-secondary shrink-0 text-[20px]">
                check_circle
              </span>
            </div>
          </a>

          <!-- Rutas y accesos principales -->
          <div class="space-y-3 pt-2">
            <div class="font-label-sm text-label-sm uppercase tracking-wider text-outline font-bold">
              Rutas de Acceso en Pasaje
            </div>
            <div class="space-y-2">
              <div class="flex items-start gap-3 p-3 rounded-xl bg-surface-container-low border border-outline-variant">
                <span class="material-symbols-outlined text-[20px] text-primary shrink-0 mt-0.5">
                  navigation
                </span>
                <div class="font-body-sm text-body-sm text-on-surface leading-snug">
                  <strong class="font-semibold">Desde la Av. Rocafuerte:</strong> Avanzar hacia el centro 4 cuadras y girar al este sobre calle Sucre.
                </div>
              </div>
              <div class="flex items-start gap-3 p-3 rounded-xl bg-surface-container-low border border-outline-variant">
                <span class="material-symbols-outlined text-[20px] text-primary shrink-0 mt-0.5">
                  turn_right
                </span>
                <div class="font-body-sm text-body-sm text-on-surface leading-snug">
                  <strong class="font-semibold">Desde Machala / Vía Panamericana:</strong> Ingreso directo por el redondel principal hacia Av. Quito, directo hasta intersección con Sucre.
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Canales Digitales Oficiales Verificados -->
        <div class="bg-surface-container-lowest p-6 rounded-3xl shadow-sm space-y-4 border border-outline-variant">
          <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-[22px] text-secondary">verified_user</span>
            <div>
              <h3 class="font-title-lg text-title-lg font-bold text-on-surface leading-tight">
                Canales Digitales Oficiales
              </h3>
              <span class="font-label-sm text-label-sm text-outline">
                Enlaces directos verificados del centro médico
              </span>
            </div>
          </div>
          <div class="space-y-2.5 pt-1">
            <!-- Instagram -->
            <a
              class="group flex items-center justify-between p-3.5 rounded-2xl bg-surface-container-low hover:bg-surface-container transition-all border border-outline-variant"
              href="https://instagram.com/hospital_sanmarcosec/"
              rel="noopener noreferrer"
              target="_blank"
            >
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-tertiary-container via-tertiary to-primary-container text-white flex items-center justify-center shadow-sm">
                  <span class="material-symbols-outlined text-[20px]">photo_camera</span>
                </div>
                <div>
                  <div class="font-title-md text-label-md font-semibold text-on-surface group-hover:text-primary transition-colors">
                    Instagram Oficial
                  </div>
                  <div class="font-body-sm text-label-sm text-outline">@hospital_sanmarcosec</div>
                </div>
              </div>
              <span class="material-symbols-outlined text-outline group-hover:text-primary text-[20px] transition-colors">
                open_in_new
              </span>
            </a>

            <!-- Facebook -->
            <a
              class="group flex items-center justify-between p-3.5 rounded-2xl bg-surface-container-low hover:bg-surface-container transition-all border border-outline-variant"
              href="https://facebook.com/hospitalsanmarcospasaje/"
              rel="noopener noreferrer"
              target="_blank"
            >
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary text-on-primary flex items-center justify-center shadow-sm">
                  <span class="material-symbols-outlined text-[20px]">public</span>
                </div>
                <div>
                  <div class="font-title-md text-label-md font-semibold text-on-surface group-hover:text-primary transition-colors">
                    Facebook Comunitario
                  </div>
                  <div class="font-body-sm text-label-sm text-outline">/hospitalsanmarcospasaje</div>
                </div>
              </div>
              <span class="material-symbols-outlined text-outline group-hover:text-primary text-[20px] transition-colors">
                open_in_new
              </span>
            </a>

            <!-- Portal Web Oficial -->
            <a
              class="group flex items-center justify-between p-3.5 rounded-2xl bg-surface-container-low hover:bg-surface-container transition-all border border-outline-variant"
              href="https://hospitalsanmarcos.com.ec/"
              rel="noopener noreferrer"
              target="_blank"
            >
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary-container text-on-primary flex items-center justify-center shadow-sm">
                  <span class="material-symbols-outlined text-[20px]">language</span>
                </div>
                <div>
                  <div class="font-title-md text-label-md font-semibold text-on-surface group-hover:text-primary transition-colors">
                    Sitio Web Oficial
                  </div>
                  <div class="font-body-sm text-label-sm text-outline">hospitalsanmarcos.com.ec</div>
                </div>
              </div>
              <span class="material-symbols-outlined text-outline group-hover:text-primary text-[20px] transition-colors">
                open_in_new
              </span>
            </a>
          </div>
        </div>

        <!-- Módulo de Hospitalidad & Atención al Usuario -->
        <div class="p-6 rounded-3xl bg-surface-container text-on-surface space-y-3 border border-outline-variant">
          <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[22px]">support_agent</span>
            <span class="font-title-md text-title-md font-bold">Oficina de Atención al Usuario</span>
          </div>
          <p class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed">
            Si requieres asistencia presencial para pacientes con movilidad reducida o acompañamiento de triaje, notifícalo a recepción al arribar en el acceso de Av. Quito.
          </p>
          <div class="pt-2 flex items-center gap-4 text-outline font-label-sm text-label-sm">
            <span class="flex items-center gap-1">
              <span class="material-symbols-outlined text-[16px] text-secondary">accessible</span>
              Acceso inclusivo
            </span>
            <span class="flex items-center gap-1">
              <span class="material-symbols-outlined text-[16px] text-secondary">local_parking</span>
              Zona de descenso
            </span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Banner de Emergencia Crítico al Pie -->
  <section class="max-w-7xl mx-auto px-gutter w-full pb-20">
    <div class="relative overflow-hidden rounded-3xl p-8 sm:p-10 bg-error-container text-on-error-container shadow-md border border-error-container">
      <div class="absolute -right-16 -top-16 w-64 h-64 bg-error/15 rounded-full blur-2xl pointer-events-none"></div>
      <div class="relative z-10 flex flex-col lg:flex-row items-center justify-between gap-6">
        <div class="flex items-start sm:items-center gap-5">
          <div class="w-16 h-16 rounded-2xl bg-error text-on-error flex items-center justify-center shrink-0 shadow-md">
            <span class="material-symbols-outlined text-[36px] animate-pulse">crisis_alert</span>
          </div>
          <div class="space-y-1">
            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-error text-on-error font-label-sm text-label-sm uppercase font-bold tracking-wider mb-1">
              <span>Alerta de Triage Inmediato</span>
            </div>
            <h3 class="font-headline-sm text-headline-sm font-bold text-on-error-container leading-tight">
              ¿Es una emergencia médica que no puede esperar?
            </h3>
            <p class="font-body-md text-body-md text-on-error-container/90 max-w-2xl leading-relaxed">
              Acude directamente al área de Urgencias 24h en nuestras instalaciones o comunícate de inmediato a nuestra línea de guardia permanente al
              <strong class="font-bold underline">098 797 3511</strong>.
            </p>
          </div>
        </div>
        <div class="flex flex-col sm:flex-row items-center gap-3.5 w-full lg:w-auto shrink-0">
          <a
            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-error text-on-error font-title-md text-title-md font-bold px-7 py-4 rounded-full hover:bg-tertiary transition-colors shadow-sm"
            href="tel:0987973511"
          >
            <span class="material-symbols-outlined text-[22px]">phone_forwarded</span>
            <span>Llamar al 098 797 3511</span>
          </a>
          <a
            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-surface-container-lowest text-on-error-container font-title-md text-title-md font-semibold px-6 py-4 rounded-full hover:bg-surface-container-low transition-colors border border-error-container"
            href="https://wa.me/593987973511?text=Emergencia%20m%C3%A9dica:%20solicito%20asistencia%20inmediata"
            rel="noopener noreferrer"
            target="_blank"
          >
            <span class="material-symbols-outlined text-[22px] text-secondary">chat</span>
            <span>WhatsApp Urgencias</span>
          </a>
        </div>
      </div>
    </div>
  </section>
</div>
