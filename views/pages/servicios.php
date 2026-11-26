<?php
declare(strict_types=1);

if (!defined('HSM_APP')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

$currentPage  = 'Servicios';
$services     = ServiceRepository::getAll();
$faqs         = FaqRepository::getAll();
$defaultFaqId = 'emergencias-24h';
?>
<div class="hospital-page-hero flex flex-col w-full">
  <!-- ENCABEZADO INSTITUCIONAL -->
  <section class="relative bg-surface overflow-hidden pt-8 pb-space-2xl">
    <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-primary-fixed/30 blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 left-12 w-64 h-64 rounded-full bg-secondary-fixed/20 blur-2xl pointer-events-none"></div>
    <div class="max-w-7xl mx-auto px-gutter relative z-10">
      <!-- BREADCRUMB -->
      <?php require __DIR__ . '/../partials/breadcrumb.php'; ?>

      <div class="hospital-hero-grid grid grid-cols-1 lg:grid-cols-12 gap-space-xl items-start">
        <div class="hospital-hero-copy lg:col-span-8 space-y-space-md">
          <h1 class="hospital-hero-title font-display text-display text-on-surface leading-tight tracking-tight">
            Servicios médicos para cuidar de ti y tu familia.
          </h1>
          <p class="hospital-hero-description font-body-lg text-body-lg text-on-surface-variant max-w-3xl leading-relaxed">
            Contamos con una infraestructura asistencial completa para brindar atención integral desde urgencias hasta intervenciones quirúrgicas de segundo nivel en Pasaje.
          </p>
        </div>
        <div class="lg:col-span-4 flex flex-col sm:flex-row lg:flex-col gap-space-sm">
          <div class="hospital-hero-card p-space-md rounded-xl bg-surface-container-low shadow-sm flex items-center gap-space-md border border-outline-variant">
            <div class="w-12 h-12 rounded-full bg-primary-container text-on-primary flex items-center justify-center shrink-0">
              <span class="material-symbols-outlined text-[24px]">verified</span>
            </div>
            <div>
              <p class="font-title-md text-title-md text-on-surface font-semibold">Nivel II Acreditado</p>
              <p class="font-body-sm text-body-sm text-on-surface-variant">
                Protocolos clínicos rigurosos y bioseguridad garantizada.
              </p>
            </div>
          </div>
          <div class="hospital-hero-card p-space-md rounded-xl bg-surface-container-low shadow-sm flex items-center gap-space-md border border-outline-variant">
            <div class="w-12 h-12 rounded-full bg-secondary text-on-secondary flex items-center justify-center shrink-0">
              <span class="material-symbols-outlined text-[24px]">support_agent</span>
            </div>
            <div>
              <p class="font-title-md text-title-md text-on-surface font-semibold">Guardia Médica 24h</p>
              <p class="font-body-sm text-body-sm text-on-surface-variant">
                Línea directa para soporte de ambulancia y triaje.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- CATÁLOGO COMPLETO DE SERVICIOS -->
  <section class="py-space-2xl bg-surface-container-lowest border-y border-outline-variant">
    <div class="max-w-7xl mx-auto px-gutter">
      <div class="flex flex-col md:flex-row md:items-end justify-between mb-space-2xl gap-space-md">
        <div>
          <span class="font-label-sm text-label-sm text-primary uppercase tracking-widest font-bold">
            Unidades Asistenciales
          </span>
          <h2 class="font-headline-lg text-headline-lg text-on-surface mt-1">
            Cartera de Servicios Hospitalarios
          </h2>
          <p class="font-body-md text-body-md text-on-surface-variant max-w-2xl mt-2">
            Disponibilidad permanente de tecnología, médicos especialistas y equipos clínicos dedicados a la salud de la comunidad orense.
          </p>
        </div>
        <div class="flex items-center gap-2 self-start md:self-auto font-label-md text-label-md text-on-surface-variant bg-surface-container-low px-4 py-2 rounded-full border border-outline-variant">
          <span class="material-symbols-outlined text-primary text-[20px]">medical_services</span>
          <span>7 unidades especializadas activas</span>
        </div>
      </div>

      <!-- GRID DE 3 COLUMNAS -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-space-lg">
        <?php foreach ($services as $service): ?>
          <div
            class="group relative rounded-xl bg-surface p-space-lg shadow-sm hover:shadow-md transition-all duration-300 flex flex-col justify-between border border-outline-variant"
          >
            <div>
              <div class="flex items-start justify-between gap-space-sm mb-space-md">
                <div
                  class="w-14 h-14 rounded-full flex items-center justify-center shrink-0 <?= ($service['id'] === 'emergencias-24h')
                      ? 'bg-error-container text-on-error-container'
                      : 'bg-surface-container-high text-primary' ?>"
                >
                  <span class="material-symbols-outlined text-[28px]"><?= e($service['icon']) ?></span>
                </div>
                <span class="inline-flex items-center px-3 py-1 rounded-full font-label-sm text-label-sm <?= e($service['tagClass'] ?? '') ?>">
                  <?= e($service['tag']) ?>
                </span>
              </div>
              <h3 class="font-headline-sm text-headline-sm text-on-surface group-hover:text-primary transition-colors">
                <?= e($service['name']) ?>
              </h3>
              <p class="mt-space-sm font-body-md text-body-md text-on-surface-variant leading-relaxed">
                <?= e($service['description']) ?>
              </p>
              <div class="mt-space-md pt-space-md space-y-2 border-t border-surface-container">
                <?php foreach ($service['features'] as $feat): ?>
                  <div class="flex items-center gap-2 font-body-sm text-body-sm text-on-surface-variant">
                    <span class="material-symbols-outlined text-[18px] text-primary">
                      check_circle
                    </span>
                    <span><?= e($feat) ?></span>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
            <div class="mt-space-xl pt-space-md flex flex-wrap items-center gap-2">
              <?php if ($service['id'] === 'emergencias-24h'): ?>
                <a
                  class="inline-flex items-center justify-center font-title-md text-label-md font-semibold bg-tertiary text-on-tertiary px-4 py-2 rounded-full hover:bg-on-tertiary-container transition-colors shadow-sm ml-auto"
                  href="tel:0987973511"
                >
                  <span class="material-symbols-outlined text-[18px] mr-1">call</span>
                  Llamar ahora
                </a>
              <?php else: ?>
                <a
                  class="inline-flex items-center justify-center font-title-md text-label-md font-semibold bg-primary-container text-on-primary px-4 py-2 rounded-full hover:bg-primary transition-colors shadow-sm ml-auto"
                  href="<?= e(url('/agendar-cita')) ?>"
                >
                  Solicitar cita
                </a>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>

        <!-- 7. HOSPITALIZACIÓN (Destacado horizontal) -->
        <div class="md:col-span-2 lg:col-span-3 group relative rounded-xl bg-surface-container p-space-xl shadow-sm hover:shadow-md transition-all duration-300 border border-outline-variant">
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-space-lg items-center">
            <div class="lg:col-span-8 space-y-space-sm">
              <div class="flex items-center gap-3">
                <div class="w-14 h-14 rounded-full bg-primary text-on-primary flex items-center justify-center shrink-0">
                  <span class="material-symbols-outlined text-[28px]">bed</span>
                </div>
                <div>
                  <span class="font-label-sm text-label-sm text-primary uppercase font-bold tracking-wider">
                    Estancia Segura
                  </span>
                  <h3 class="font-headline-sm text-headline-sm text-on-surface">
                    Hospitalización
                  </h3>
                </div>
              </div>
              <p class="font-body-md text-body-md text-on-surface-variant max-w-3xl leading-relaxed">
                Habitaciones acondicionadas con monitoreo continuo, enfermería dedicada y atención médica para una recuperación segura. Diseñadas para garantizar el descanso del paciente y la comodidad de sus acompañantes.
              </p>
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-space-sm pt-space-sm">
                <div class="p-3 rounded-lg bg-surface-container-lowest/80 flex items-center gap-2.5 border border-outline-variant">
                  <span class="material-symbols-outlined text-primary text-[20px]">monitor_heart</span>
                  <span class="font-body-sm text-body-sm font-semibold text-on-surface">
                    Monitoreo continuo 24/7
                  </span>
                </div>
                <div class="p-3 rounded-lg bg-surface-container-lowest/80 flex items-center gap-2.5 border border-outline-variant">
                  <span class="material-symbols-outlined text-primary text-[20px]">diversity_1</span>
                  <span class="font-body-sm text-body-sm font-semibold text-on-surface">
                    Enfermería especializada
                  </span>
                </div>
                <div class="p-3 rounded-lg bg-surface-container-lowest/80 flex items-center gap-2.5 border border-outline-variant">
                  <span class="material-symbols-outlined text-primary text-[20px]">air</span>
                  <span class="font-body-sm text-body-sm font-semibold text-on-surface">
                    Climatización &amp; Aislamiento
                  </span>
                </div>
              </div>
            </div>
            <div class="lg:col-span-4 flex flex-col sm:flex-row lg:flex-col justify-end items-stretch gap-space-sm">
              <a
                class="inline-flex items-center justify-center font-title-md text-label-md font-semibold bg-primary text-on-primary px-6 py-3 rounded-full hover:bg-on-primary-container transition-colors shadow-sm text-center"
                href="<?= e(url('/agendar-cita')) ?>"
              >
                Solicitar cita de valoración
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- PREGUNTAS FRECUENTES (ACORDEÓN INTERACTIVO) -->
  <section class="py-space-2xl bg-surface-container-lowest border-y border-outline-variant">
    <div class="max-w-4xl mx-auto px-gutter">
      <div class="text-center mb-space-xl">
        <span class="font-label-sm text-label-sm text-primary uppercase tracking-widest font-bold">
          Orientación al Paciente
        </span>
        <h2 class="font-headline-lg text-headline-lg text-on-surface mt-1">
          Preguntas Frecuentes sobre Servicios
        </h2>
        <p class="font-body-md text-body-md text-on-surface-variant mt-2 max-w-xl mx-auto">
          Resolvemos tus dudas habituales sobre protocolos de acceso, preparación y funcionamiento asistencial.
        </p>
      </div>
      <div class="space-y-space-sm" id="faqs-accordion">
        <?php foreach ($faqs as $faq): ?>
          <?php $isOpen = ($faq['id'] === $defaultFaqId); ?>
          <div
            data-faq-item="<?= e($faq['id']) ?>"
            class="rounded-xl bg-surface-container-low transition-all border border-outline-variant overflow-hidden"
          >
            <button
              type="button"
              data-faq-toggle="<?= e($faq['id']) ?>"
              class="w-full text-left p-space-lg flex items-center justify-between gap-space-md focus:outline-none hover:bg-surface-container transition-colors"
              aria-expanded="<?= $isOpen ? 'true' : 'false' ?>"
            >
              <span class="font-title-md text-title-md text-on-surface font-semibold">
                <?= e($faq['question']) ?>
              </span>
              <span
                data-faq-icon="<?= e($faq['id']) ?>"
                class="material-symbols-outlined text-primary text-[24px] transition-transform duration-200 shrink-0 <?= $isOpen ? 'rotate-180' : '' ?>"
              >
                expand_more
              </span>
            </button>
            <div
              data-faq-answer="<?= e($faq['id']) ?>"
              class="<?= $isOpen ? '' : 'hidden ' ?>px-space-lg pb-space-lg text-on-surface-variant font-body-md text-body-md leading-relaxed border-t border-outline-variant/60 pt-3"
            >
              <?= e($faq['answer']) ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- LLAMADO A LA ACCIÓN FINAL -->
  <section class="py-space-2xl bg-surface relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-gutter">
      <div class="relative rounded-2xl bg-gradient-to-br from-inverse-surface to-on-surface text-surface p-space-xl md:p-space-2xl shadow-xl overflow-hidden border border-outline-variant">
        <div class="absolute -right-20 -bottom-20 w-80 h-80 rounded-full bg-primary/20 blur-3xl pointer-events-none"></div>
        <div class="absolute -left-10 -top-10 w-60 h-60 rounded-full bg-secondary-fixed/10 blur-2xl pointer-events-none"></div>
        <div class="relative z-10 max-w-3xl space-y-space-md">
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-surface-container-low/10 text-secondary-fixed font-label-sm text-label-sm uppercase tracking-wider font-semibold">
            <span class="material-symbols-outlined text-[16px]">contact_support</span>
            Orientación Asistencial Inmediata
          </div>
          <h2 class="font-headline-lg text-headline-lg text-surface tracking-tight leading-tight">
            ¿Tienes dudas sobre alguno de nuestros servicios asistenciales?
          </h2>
          <p class="font-body-lg text-body-lg text-surface-container-highest max-w-2xl leading-relaxed">
            Nuestro equipo de admisiones y atención al usuario está listo para guiarte en costos, requisitos clínicos, cobertura de seguros y preparación para estudios médicos.
          </p>
          <div class="pt-space-md flex flex-wrap items-center gap-space-md">
            <a
              class="inline-flex items-center justify-center font-title-md text-label-md font-semibold bg-primary-container text-on-primary px-8 py-3.5 rounded-full hover:bg-primary transition-all duration-200 shadow-md hover:shadow"
              href="<?= e(url('/agendar-cita')) ?>"
            >
              <span class="material-symbols-outlined mr-2 text-[20px]">calendar_today</span>
              Agendar cita médica
            </a>
            <a
              class="inline-flex items-center justify-center font-title-md text-label-md font-semibold bg-secondary text-on-secondary px-8 py-3.5 rounded-full hover:bg-on-secondary-container transition-all duration-200 shadow-md"
              href="https://wa.me/593987973511"
              rel="noopener noreferrer"
              target="_blank"
            >
              <span class="material-symbols-outlined mr-2 text-[20px]">chat</span>
              Consultar por WhatsApp (098 797 3511)
            </a>
          </div>
          <div class="pt-space-sm flex items-center gap-4 text-surface-container-highest font-body-sm text-body-sm">
            <span class="flex items-center gap-1.5">
              <span class="material-symbols-outlined text-[16px] text-secondary-fixed">
                pin_drop
              </span>
              Av. Quito y Sucre, Pasaje
            </span>
            <span>•</span>
            <span class="flex items-center gap-1.5">
              <span class="material-symbols-outlined text-[16px] text-secondary-fixed">
                phone_in_talk
              </span>
              098 797 3511
            </span>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>
