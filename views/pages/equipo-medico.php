<?php
declare(strict_types=1);

if (!defined('HSM_APP')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

$currentPage   = 'Equipo médico';
$doctors       = DoctorRepository::getAll();
$categories    = DoctorRepository::getCategoriesWithCounts();
$initialSearch = sanitize_query_param($_GET['search'] ?? null, 80);
$totalDoctors  = count($doctors);
?>
<div class="hospital-page-hero flex flex-col w-full">
  <!-- Hero Section -->
  <section class="relative w-full bg-surface-container-low pt-8 pb-6 overflow-hidden">
    <div class="absolute -right-24 -top-24 w-96 h-96 rounded-full bg-surface-container-high/60 blur-3xl pointer-events-none"></div>
    <div class="absolute left-1/3 -bottom-20 w-80 h-80 rounded-full bg-primary-fixed/20 blur-3xl pointer-events-none"></div>
    <div class="max-w-7xl mx-auto px-gutter relative z-10">
      <?php require __DIR__ . '/../partials/breadcrumb.php'; ?>

      <!-- Editorial Header Layout -->
      <div class="hospital-hero-grid grid grid-cols-1 lg:grid-cols-12 gap-space-xl items-start">
        <div class="hospital-hero-copy lg:col-span-8 space-y-space-md">
          <h1 class="hospital-hero-title font-display text-display text-on-surface tracking-tight leading-none">
            Nuestro Equipo Médico
          </h1>
          <p class="hospital-hero-description font-body-lg text-body-lg text-on-surface-variant max-w-2xl leading-relaxed">
            Conoce a los profesionales que forman parte de nuestro directorio y encuentra la atención especializada que necesitas.
          </p>
        </div>

        <!-- Metrics pills -->
        <div class="lg:col-span-4 flex flex-col sm:flex-row lg:flex-col gap-space-md">
          <div class="hospital-hero-card p-space-lg rounded-xl bg-surface-container-lowest shadow-sm flex items-center gap-space-md border border-outline-variant">
            <div class="w-12 h-12 rounded-full bg-surface-container-high text-primary flex items-center justify-center shrink-0">
              <span class="material-symbols-outlined text-[26px]">medical_services</span>
            </div>
            <div>
              <p class="font-headline-sm text-headline-sm text-on-surface font-bold leading-tight">
                22 Especialistas
              </p>
              <p class="font-body-sm text-body-sm text-on-surface-variant">
                Cuerpo facultativo acreditado
              </p>
            </div>
          </div>
          <div class="hospital-hero-card p-space-lg rounded-xl bg-surface-container-lowest shadow-sm flex items-center gap-space-md border border-outline-variant">
            <div class="w-12 h-12 rounded-full bg-secondary-fixed/50 text-secondary flex items-center justify-center shrink-0">
              <span class="material-symbols-outlined text-[26px]">schedule</span>
            </div>
            <div>
              <p class="font-headline-sm text-headline-sm text-on-surface font-bold leading-tight">
                Turnos Programados
              </p>
              <p class="font-body-sm text-body-sm text-on-surface-variant">
                Lunes a sábado en Pasaje, El Oro
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Search & Filtering Hub -->
  <section class="w-full bg-surface-container-lowest py-5 px-gutter border-y border-outline-variant">
    <div class="max-w-7xl mx-auto flex flex-col gap-4">
      <div class="grid grid-cols-1 md:grid-cols-12 gap-space-md items-center">
        <!-- Live Search Input -->
        <div class="md:col-span-7 relative">
          <label class="sr-only" for="doctorSearchInput">
            Buscar por nombre o especialidad
          </label>
          <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-outline">
            <span class="material-symbols-outlined text-[22px]">search</span>
          </div>
          <input
            class="w-full pl-12 pr-10 py-3.5 bg-surface-container-low rounded-xl font-body-md text-body-md text-on-surface placeholder:text-outline focus-primary focus:bg-surface transition-all shadow-sm border border-outline-variant"
            id="doctorSearchInput"
            placeholder="Buscar médico por nombre o especialidad..."
            type="text"
            value="<?= e($initialSearch) ?>"
          />
          <button
            id="doctor-search-clear"
            type="button"
            aria-label="Limpiar búsqueda"
            class="<?= $initialSearch !== '' ? '' : 'hidden ' ?>absolute right-3.5 top-1/2 -translate-y-1/2 text-outline hover:text-on-surface p-1"
          >
            <span class="material-symbols-outlined text-[18px]">close</span>
          </button>
        </div>

        <!-- Counter -->
        <div class="md:col-span-5 flex items-center justify-start md:justify-end gap-space-sm text-on-surface-variant font-label-md text-label-md">
          <span class="inline-block w-2.5 h-2.5 rounded-full bg-primary-container"></span>
          <span id="doctor-results-count">
            <?= $totalDoctors === 1 ? 'Mostrando 1 especialista acreditado' : 'Mostrando ' . $totalDoctors . ' especialistas acreditados' ?>
          </span>
        </div>
      </div>

      <!-- Categories Filter Tabs -->
      <div
        aria-label="Filtrar médicos por especialidad"
        class="flex items-center gap-2 overflow-x-auto pb-2 no-scrollbar"
        role="tablist"
      >
        <?php foreach ($categories as $cat): ?>
          <?php $isAll = ($cat['id'] === 'all'); ?>
          <button
            type="button"
            data-doctor-cat="<?= e($cat['id']) ?>"
            class="px-5 py-2 rounded-full font-label-md text-label-md transition-all whitespace-nowrap shadow-sm <?= $isAll
                ? 'bg-primary text-on-primary'
                : 'bg-surface-container text-on-surface hover:bg-surface-container-high' ?>"
          >
            <?= e($cat['label']) ?>
          </button>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Medical Directory Grid -->
  <section class="w-full bg-surface py-8 px-gutter">
    <div class="max-w-7xl mx-auto flex flex-col gap-space-xl">
      <!-- Estado vacío cuando no hay coincidencias -->
      <div
        id="doctor-empty-state"
        class="hidden flex-col items-center justify-center text-center py-space-2xl bg-surface-container-lowest rounded-xl p-space-xl border border-outline-variant"
      >
        <span class="material-symbols-outlined text-outline text-[48px] mb-2">
          person_search
        </span>
        <h4 class="font-title-lg text-title-lg text-on-surface font-bold">
          No se encontraron especialistas
        </h4>
        <p class="font-body-md text-body-md text-on-surface-variant max-w-md mt-1">
          Intenta ajustar los términos de búsqueda o selecciona otra categoría de especialidad médica.
        </p>
        <button
          id="doctor-reset-btn"
          class="mt-space-md px-6 py-2 rounded-full bg-primary text-on-primary font-label-md text-label-md font-semibold hover:bg-on-primary-container transition-colors"
          type="button"
        >
          Restablecer filtros
        </button>
      </div>

      <!-- Cuadrícula con los 29 médicos -->
      <div id="doctor-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-space-lg">
        <?php foreach ($doctors as $doctor): ?>
          <?php
          $categoryBadgeLabel = DoctorRepository::getCategoryBadgeLabel($doctor);
          $isAccredited       = !empty($doctor['accredited']);
          $appointmentUrl     = url('/agendar-cita') . '?doctor=' . rawurlencode((string) $doctor['name']);
          ?>
          <article
            data-doctor-card="true"
            data-id="<?= e($doctor['id']) ?>"
            data-name="<?= e($doctor['name']) ?>"
            data-specialty="<?= e($doctor['specialty']) ?>"
            data-category="<?= e($doctor['category']) ?>"
            data-badge="<?= e($categoryBadgeLabel) ?>"
            data-initials="<?= e($doctor['initials']) ?>"
            data-location="<?= e($doctor['location']) ?>"
            data-accredited="<?= $isAccredited ? '1' : '0' ?>"
            data-description="<?= e($doctor['description']) ?>"
            data-appointment-url="<?= e($appointmentUrl) ?>"
            class="flex flex-col bg-surface-container-lowest rounded-xl p-space-lg shadow-sm hover:shadow-md transition-all border border-outline-variant group"
          >
            <div class="flex items-center justify-between mb-space-md">
              <div class="w-16 h-16 rounded-full bg-gradient-to-br from-primary-fixed to-primary text-surface flex items-center justify-center font-bold text-title-lg tracking-wide shadow-sm shrink-0">
                <?= e($doctor['initials']) ?>
              </div>
              <span class="bg-surface-container text-primary font-label-sm text-label-sm px-3 py-1 rounded-full font-semibold">
                <?= e($categoryBadgeLabel) ?>
              </span>
            </div>
            <div class="flex flex-col flex-grow">
              <h3 class="font-title-md text-title-md text-on-surface font-bold leading-snug">
                <?= e($doctor['name']) ?>
              </h3>
              <p class="font-label-md text-label-md text-primary font-semibold mt-1">
                <?= e($doctor['specialty']) ?>
              </p>
              <div class="mt-space-md space-y-1.5 text-on-surface-variant font-body-sm text-body-sm">
                <div class="flex items-center gap-2">
                  <span class="material-symbols-outlined text-[18px] text-outline shrink-0">
                    domain
                  </span>
                  <span><?= e($doctor['location']) ?></span>
                </div>
                <div class="flex items-center gap-2">
                  <?php if ($isAccredited): ?>
                    <span class="material-symbols-outlined text-[18px] text-secondary shrink-0">
                      verified
                    </span>
                    <span class="text-on-surface font-medium">Médico Acreditado</span>
                  <?php else: ?>
                    <span class="material-symbols-outlined text-[18px] text-primary shrink-0">
                      schedule
                    </span>
                    <span class="text-on-surface-variant font-medium">Disponibilidad por confirmar</span>
                  <?php endif; ?>
                </div>
              </div>
              <div class="mt-space-lg pt-space-md flex flex-col gap-2">
                <button
                  type="button"
                  data-open-doctor-modal="<?= e($doctor['id']) ?>"
                  class="inline-flex items-center justify-center gap-1 bg-surface-container text-primary font-title-md text-label-md font-semibold px-4 py-2 rounded-full hover:bg-surface-container-high transition-colors"
                >
                  <span>Ver perfil</span>
                  <span class="material-symbols-outlined text-[16px]">visibility</span>
                </button>
                <a
                  href="<?= e($appointmentUrl) ?>"
                  class="inline-flex items-center justify-center gap-2 bg-primary-container text-on-primary font-title-md text-label-md font-semibold px-4 py-2.5 rounded-full hover:bg-primary transition-colors shadow-sm"
                >
                  <span class="material-symbols-outlined text-[18px]">calendar_today</span>
                  <span>Solicitar cita</span>
                </a>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Doctor Profile Modal -->
  <div
    id="doctor-profile-modal"
    class="hidden fixed inset-0 z-50 items-center justify-center bg-inverse-surface/60 backdrop-blur-sm p-4 overflow-y-auto animate-fadeIn"
    role="dialog"
    aria-modal="true"
    aria-labelledby="modal-doctor-name"
  >
    <div class="relative w-full max-w-lg bg-surface-container-lowest rounded-2xl shadow-2xl p-space-lg md:p-space-xl border border-outline-variant flex flex-col gap-space-md transition-all">
      <button
        type="button"
        data-close-doctor-modal="true"
        class="absolute top-4 right-4 text-outline hover:text-on-surface p-1 rounded-full hover:bg-surface-container transition-colors"
        aria-label="Cerrar modal"
      >
        <span class="material-symbols-outlined text-[24px]">close</span>
      </button>
      <div class="flex items-center gap-space-md">
        <div
          id="modal-doctor-initials"
          class="w-20 h-20 rounded-full bg-gradient-to-br from-primary-fixed to-primary text-surface flex items-center justify-center font-bold text-headline-sm tracking-wide shadow-sm shrink-0"
        >
          JC
        </div>
        <div>
          <span
            id="modal-doctor-badge"
            class="inline-block px-2.5 py-0.5 rounded-full bg-surface-container text-primary font-label-sm text-label-sm font-semibold mb-1"
          >
            Profesional Acreditado
          </span>
          <h3 id="modal-doctor-name" class="font-title-lg text-title-lg text-on-surface font-bold leading-tight">
            Nombre del Médico
          </h3>
          <p id="modal-doctor-specialty" class="font-label-md text-label-md text-primary font-semibold">
            Especialidad
          </p>
        </div>
      </div>
      <div class="p-space-md rounded-xl bg-surface-container-low text-on-surface-variant font-body-sm text-body-sm leading-relaxed space-y-2 border border-outline-variant">
        <p class="flex items-center gap-2 font-semibold text-on-surface">
          <span class="material-symbols-outlined text-[18px] text-primary">location_on</span>
          <span id="modal-doctor-location">Hospital San Marcos · Pasaje, El Oro</span>
        </p>
        <p id="modal-doctor-description"></p>
        <p class="text-xs text-outline">
          Horarios y turnos de consulta sujetos a confirmación y disponibilidad en la mesa de admisión.
        </p>
      </div>
      <div class="flex flex-col sm:flex-row gap-space-sm pt-space-xs">
        <a
          id="modal-doctor-appointment-link"
          href="<?= e(url('/agendar-cita')) ?>"
          class="flex-1 inline-flex items-center justify-center gap-2 bg-primary-container text-on-primary font-title-md text-label-md font-semibold px-5 py-3 rounded-full hover:bg-primary transition-colors shadow-sm"
        >
          <span class="material-symbols-outlined text-[18px]">calendar_today</span>
          <span>Solicitar cita con este profesional</span>
        </a>
        <button
          type="button"
          data-close-doctor-modal="true"
          class="px-5 py-3 rounded-full bg-surface-container text-on-surface hover:bg-surface-container-high font-label-md text-label-md font-semibold transition-colors"
        >
          Cerrar
        </button>
      </div>
    </div>
  </div>

  <!-- Institutional Transparency Notice -->
  <section class="w-full bg-surface-container-low px-gutter py-space-xl border-t border-outline-variant">
    <div class="max-w-5xl mx-auto rounded-xl bg-surface-container-lowest p-space-lg md:p-space-xl shadow-sm flex flex-col md:flex-row items-center md:items-start gap-space-lg border border-outline-variant">
      <div class="w-14 h-14 rounded-full bg-surface-container-high text-primary flex items-center justify-center shrink-0">
        <span class="material-symbols-outlined text-[30px]">info</span>
      </div>
      <div class="space-y-2 text-center md:text-left">
        <span class="font-label-sm text-label-sm uppercase tracking-wider font-bold text-primary">
          Aviso de transparencia institucional
        </span>
        <p class="font-body-md text-body-md text-on-surface leading-relaxed">
          El directorio médico se actualiza de manera periódica conforme a las incorporaciones y turnos de consulta externa del Hospital San Marcos en Pasaje.
        </p>
        <p class="font-body-sm text-body-sm text-on-surface-variant">
          Para confirmar disponibilidad en tiempo real o solicitar interconsultas quirúrgicas de urgencia, comunícate directamente con nuestra mesa central de admisiones.
        </p>
      </div>
    </div>
  </section>

  <!-- Final CTA Banner -->
  <section class="w-full bg-surface py-8 px-gutter">
    <div class="max-w-7xl mx-auto rounded-2xl bg-gradient-to-br from-inverse-surface via-inverse-surface to-primary text-surface p-space-xl md:p-space-2xl shadow-xl relative overflow-hidden border border-outline-variant">
      <div class="absolute -right-20 -bottom-20 w-80 h-80 rounded-full bg-primary-container/10 blur-2xl pointer-events-none"></div>
      <div class="relative z-10 flex flex-col lg:flex-row items-center justify-between gap-space-xl">
        <div class="space-y-space-md text-center lg:text-left max-w-2xl">
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-surface/10 text-secondary-fixed font-label-sm text-label-sm">
            <span class="material-symbols-outlined text-[16px]">support_agent</span>
            <span>Mesa de Admisiones y Citas</span>
          </div>
          <h2 class="font-display text-headline-lg text-surface tracking-tight leading-tight">
            ¿Deseas consultar la disponibilidad de un especialista?
          </h2>
          <p class="font-body-lg text-body-lg text-surface-container-highest leading-relaxed">
            Nuestro equipo de atención al paciente está listo para orientarte en la asignación de tu consulta médica presencial en Pasaje.
          </p>
        </div>
        <div class="flex flex-col sm:flex-row gap-space-md w-full lg:w-auto shrink-0 justify-center">
          <a
            href="<?= e(url('/agendar-cita')) ?>"
            class="inline-flex items-center justify-center gap-2 bg-primary-container text-on-primary font-title-md text-label-md font-semibold px-8 py-3.5 rounded-full hover:bg-primary transition-all shadow-md hover:scale-[1.02]"
          >
            <span class="material-symbols-outlined text-[20px]">calendar_month</span>
            <span>Agendar cita médica</span>
          </a>
          <a
            class="inline-flex items-center justify-center gap-2 bg-secondary text-on-secondary font-title-md text-label-md font-semibold px-8 py-3.5 rounded-full hover:bg-on-secondary-container transition-all shadow-md hover:scale-[1.02]"
            href="https://wa.me/593987973511"
            rel="noopener noreferrer"
            target="_blank"
          >
            <span class="material-symbols-outlined text-[20px]">chat</span>
            <span>Contactar admisión por WhatsApp</span>
          </a>
        </div>
      </div>
    </div>
  </section>
</div>
