<?php
declare(strict_types=1);

if (!defined('HSM_APP')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

$currentPage      = 'Especialidades';
$specialties      = SpecialtyRepository::getAll();
$filterCategories = SpecialtyRepository::getFilterCategories();
$totalSpecialties = count($specialties);
?>
<div class="hospital-page-hero flex flex-col w-full">
  <!-- Top Ambient Glow Decorator & Hero -->
  <div class="relative w-full overflow-hidden bg-surface-container-low pb-space-2xl pt-8">
    <div class="absolute -top-32 right-10 w-96 h-96 rounded-full bg-primary/10 blur-3xl pointer-events-none"></div>
    <div class="absolute top-24 -left-20 w-80 h-80 rounded-full bg-secondary-fixed/30 blur-2xl pointer-events-none"></div>
    <div class="max-w-7xl mx-auto px-gutter relative z-10">
      <!-- Breadcrumb -->
      <?php require __DIR__ . '/../partials/breadcrumb.php'; ?>

      <!-- Page Title & Institutional Premise -->
      <div class="hospital-hero-grid grid grid-cols-1 lg:grid-cols-12 gap-space-xl items-start">
        <div class="hospital-hero-copy lg:col-span-8 space-y-space-md">
          <h1 class="hospital-hero-title font-headline-lg text-display text-on-surface leading-tight tracking-tight">
            Encuentra la especialidad médica que necesitas.
          </h1>
          <p class="hospital-hero-description font-body-lg text-body-lg text-on-surface-variant max-w-2xl leading-relaxed">
            Contamos con distintas áreas de atención especializada para acompañarte en el cuidado de tu salud, con equipamiento clínico moderno y médicos especialistas acreditados.
          </p>
        </div>
        <div class="lg:col-span-4 flex flex-col sm:flex-row lg:flex-col gap-space-sm justify-end">
          <div class="hospital-hero-card bg-surface-container-lowest p-space-md rounded-xl shadow-sm flex items-center gap-space-md border border-outline-variant">
            <div class="w-12 h-12 rounded-full bg-surface-container-high text-primary flex items-center justify-center shrink-0">
              <span class="material-symbols-outlined text-[24px]">verified_user</span>
            </div>
            <div>
              <span class="font-title-md text-title-md font-bold text-on-surface block leading-tight">
                <?= $totalSpecialties ?> Especialidades
              </span>
              <span class="font-body-sm text-body-sm text-outline">
                Atención ambulatoria, quirúrgica y urgencias
              </span>
            </div>
          </div>
          <div class="hospital-hero-card bg-surface-container-lowest p-space-md rounded-xl shadow-sm flex items-center gap-space-md border border-outline-variant">
            <div class="w-12 h-12 rounded-full bg-error-container text-on-error-container flex items-center justify-center shrink-0">
              <span class="material-symbols-outlined text-[24px]">emergency</span>
            </div>
            <div>
              <span class="font-title-md text-title-md font-bold text-on-surface block leading-tight">
                Soporte Hospitalario 24/7
              </span>
              <span class="font-body-sm text-body-sm text-outline">
                Triage y hospitalización continua
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Search & Category Filters Panel -->
      <div class="mt-space-xl bg-surface-container-lowest p-space-lg rounded-2xl shadow-md border border-outline-variant">
        <!-- Search Input -->
        <div class="relative w-full mb-space-lg" role="search">
          <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline text-[22px]">
            search
          </span>
          <input
            id="specialty-search-input"
            aria-label="Buscar especialidad médica"
            class="w-full pl-12 pr-10 py-3.5 bg-surface-container-low text-on-surface placeholder:text-outline rounded-xl font-body-md text-body-md focus-primary focus:ring-2 focus:ring-primary-container shadow-inner transition-all"
            placeholder="Buscar especialidad médica por nombre..."
            type="text"
            value=""
          />
          <button
            id="specialty-search-clear"
            aria-label="Limpiar búsqueda"
            class="hidden absolute right-3.5 top-1/2 -translate-y-1/2 text-outline hover:text-on-surface p-1 rounded-full transition-colors"
            type="button"
          >
            <span class="material-symbols-outlined text-[18px]">close</span>
          </button>
        </div>

        <!-- Filter Chips Wrap -->
        <div class="flex flex-col gap-2">
          <div class="flex items-center justify-between">
            <span class="font-label-sm text-label-sm uppercase tracking-wider text-outline">
              Filtrar por grupo de atención:
            </span>
            <span id="specialty-results-count" class="font-label-sm text-label-sm font-semibold text-primary">
              <?= $totalSpecialties === 1 ? 'Mostrando 1 especialidad' : 'Mostrando ' . $totalSpecialties . ' especialidades' ?>
            </span>
          </div>
          <div class="flex flex-wrap items-center gap-2 pt-1" role="tablist">
            <?php foreach ($filterCategories as $cat): ?>
              <?php $isAll = ($cat['id'] === 'all'); ?>
              <button
                type="button"
                data-specialty-cat="<?= e($cat['id']) ?>"
                aria-pressed="<?= $isAll ? 'true' : 'false' ?>"
                class="px-4 py-2 rounded-full font-label-md text-label-md transition-all shadow-sm focus-primary <?= $isAll
                    ? 'bg-primary text-on-primary font-bold'
                    : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container hover:text-on-surface' ?>"
              >
                <?= e($cat['label']) ?>
              </button>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Medical Specialties Grid Section -->
  <section class="max-w-7xl mx-auto px-gutter py-space-2xl w-full">
    <!-- Estado vacío cuando la búsqueda no arroja resultados -->
    <div
      id="specialty-empty-state"
      class="hidden text-center py-space-2xl bg-surface-container-lowest rounded-2xl shadow-sm border border-outline-variant"
    >
      <span class="material-symbols-outlined text-[48px] text-outline mb-2">manage_search</span>
      <h3 class="font-headline-sm text-headline-sm text-on-surface">No encontramos coincidencias</h3>
      <p class="font-body-md text-body-md text-on-surface-variant max-w-md mx-auto mt-1 mb-space-md">
        Intenta con otro término clínico o restablece los filtros para ver la nómina completa de especialidades.
      </p>
      <button
        id="specialty-reset-btn"
        class="px-5 py-2.5 rounded-full bg-primary-container text-on-primary font-title-md text-label-md font-semibold hover:bg-primary transition-colors"
        type="button"
      >
        Ver todas las especialidades
      </button>
    </div>

    <!-- Cuadrícula de las 17 especialidades -->
    <div id="specialty-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-space-lg">
      <?php foreach ($specialties as $spec): ?>
        <?php
        $badgeType     = $spec['badgeType'] ?? 'default';
        $highlightText = $spec['highlightText'] ?? null;
        $badgeClasses  = ($badgeType === 'highlight' || $badgeType === 'pediatria')
            ? 'bg-secondary-container text-on-secondary-container font-bold'
            : 'bg-surface-container text-primary';
        ?>
        <article
          data-specialty-card="true"
          data-category="<?= e($spec['category']) ?>"
          data-name="<?= e($spec['name']) ?>"
          data-description="<?= e($spec['description']) ?>"
          class="group flex flex-col justify-between bg-surface-container-lowest p-space-lg rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 border border-outline-variant <?= $highlightText ? 'ring-2 ring-primary-container/30' : '' ?>"
        >
          <div>
            <div class="flex items-start justify-between gap-3 mb-space-md">
              <div class="w-14 h-14 rounded-2xl bg-surface-container-low text-primary flex items-center justify-center group-hover:bg-primary group-hover:text-on-primary transition-colors shadow-inner">
                <span class="material-symbols-outlined text-[30px]"><?= e($spec['icon']) ?></span>
              </div>
              <span class="px-2.5 py-1 rounded-full text-xs font-semibold <?= $badgeClasses ?>">
                <?= e($spec['badge']) ?>
              </span>
            </div>
            <h2 class="font-headline-sm text-title-lg font-bold text-on-surface mb-2">
              <?= e($spec['name']) ?>
            </h2>
            <p class="font-body-md text-body-sm text-on-surface-variant leading-relaxed mb-space-md">
              <?= e($spec['description']) ?>
            </p>
            <?php if ($highlightText): ?>
              <div class="bg-surface-container-low p-2.5 rounded-lg flex items-center gap-2 mb-space-md border border-outline-variant">
                <span class="material-symbols-outlined text-[18px] text-primary shrink-0">
                  check_circle
                </span>
                <span class="font-label-sm text-label-sm font-semibold text-primary">
                  <?= e($highlightText) ?>
                </span>
              </div>
            <?php endif; ?>
          </div>
          <div class="pt-space-md mt-auto flex items-center gap-2">
            <a
              href="<?= e(url('/equipo-medico') . '?search=' . rawurlencode($spec['name'])) ?>"
              class="w-1/2 text-center py-2.5 px-3 rounded-full bg-surface-container-low text-primary hover:bg-surface-container font-label-md text-label-md font-semibold transition-colors"
            >
              Ver especialistas
            </a>
            <a
              href="<?= e(url('/agendar-cita')) ?>"
              class="w-1/2 text-center py-2.5 px-3 rounded-full bg-primary-container text-on-primary hover:bg-primary font-label-md text-label-md font-semibold transition-colors shadow-sm"
            >
              Solicitar cita
            </a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- Sección Aclaratoria Institucional (Cirugía Laparoscópica) -->
  <section class="max-w-7xl mx-auto px-gutter pb-space-2xl w-full">
    <div class="bg-surface-container-high/60 rounded-2xl p-space-lg md:p-space-xl relative overflow-hidden shadow-sm border border-outline-variant">
      <div class="absolute -right-16 -bottom-16 w-64 h-64 bg-primary-container/10 rounded-full blur-2xl pointer-events-none"></div>
      <div class="relative z-10 grid grid-cols-1 md:grid-cols-12 gap-space-lg items-center">
        <div class="md:col-span-2 flex justify-start md:justify-center">
          <div class="w-16 h-16 rounded-2xl bg-primary text-on-primary flex items-center justify-center shadow-md">
            <span class="material-symbols-outlined text-[36px]">biotech</span>
          </div>
        </div>
        <div class="md:col-span-7 space-y-1">
          <div class="inline-flex items-center gap-1.5 text-primary font-label-sm text-label-sm uppercase tracking-wider font-bold">
            <span class="material-symbols-outlined text-[16px]">info</span>
            <span>Nota Institucional de Procedimientos</span>
          </div>
          <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold">
            Procedimiento Quirúrgico Laparoscópico
          </h3>
          <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
            La cirugía laparoscópica se realiza como procedimiento quirúrgico especializado dentro del área de Cirugía General, ofreciendo menor tiempo de recuperación y menor dolor postoperatorio para nuestros pacientes de Pasaje y El Oro.
          </p>
        </div>
        <div class="md:col-span-3 flex md:justify-end">
          <a
            href="<?= e(url('/servicios')) ?>"
            class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-full bg-primary text-on-primary font-title-md text-label-md font-semibold hover:bg-on-primary-container transition-colors shadow-sm"
          >
            <span>Conoce los quirófanos</span>
            <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- Llamado a la Acción Final (Banner de Orientación) -->
  <section class="w-full bg-surface-container-low py-space-2xl border-t border-outline-variant">
    <div class="max-w-7xl mx-auto px-gutter">
      <div class="bg-surface-container-lowest rounded-3xl p-space-xl md:p-space-2xl shadow-md relative overflow-hidden border border-outline-variant">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-space-xl items-center">
          <div class="lg:col-span-8 space-y-space-md">
            <span class="inline-block px-3 py-1 rounded-full bg-secondary-fixed text-on-secondary-fixed-variant font-label-sm text-label-sm uppercase tracking-wider font-bold">
              Orientación médica personalizada
            </span>
            <h2 class="font-headline-md text-display-mobile md:text-headline-lg text-on-surface leading-tight font-bold">
              ¿No estás seguro de qué especialidad necesitas consultar?
            </h2>
            <p class="font-body-lg text-body-lg text-on-surface-variant max-w-2xl leading-relaxed">
              Nuestro equipo de admisión y triage médico ambulatorio te orienta de manera rápida para que agendes con el especialista indicado según tus síntomas.
            </p>
            <div class="flex flex-wrap items-center gap-space-md pt-2">
              <a
                class="inline-flex items-center justify-center gap-2 bg-secondary text-on-secondary px-6 py-3.5 rounded-full font-title-md text-label-md font-semibold shadow-md hover:scale-[1.02] transition-transform"
                href="https://wa.me/593987973511"
                rel="noopener noreferrer"
                target="_blank"
              >
                <span class="material-symbols-outlined text-[20px]">chat</span>
                <span>Orientación por WhatsApp</span>
              </a>
              <a
                class="inline-flex items-center justify-center gap-2 bg-primary-container text-on-primary px-6 py-3.5 rounded-full font-title-md text-label-md font-semibold shadow-md hover:bg-primary transition-colors"
                href="<?= e(url('/agendar-cita')) ?>"
              >
                <span class="material-symbols-outlined text-[20px]">calendar_month</span>
                <span>Agendar cita médica</span>
              </a>
            </div>
          </div>
          <div class="lg:col-span-4 flex flex-col gap-3">
            <div class="bg-surface-container-low p-space-md rounded-2xl border border-outline-variant">
              <div class="flex items-center gap-3 mb-2">
                <span class="material-symbols-outlined text-primary text-[24px]">schedule</span>
                <span class="font-title-md text-title-md font-bold text-on-surface">
                  Consultas Externas
                </span>
              </div>
              <p class="font-body-sm text-body-sm text-on-surface-variant">
                Lunes a Sábado con turnos programados y confirmación previa vía telefónica o WhatsApp.
              </p>
            </div>
            <div class="bg-surface-container-low p-space-md rounded-2xl border border-outline-variant">
              <div class="flex items-center gap-3 mb-2">
                <span class="material-symbols-outlined text-error text-[24px]">call</span>
                <span class="font-title-md text-title-md font-bold text-on-surface">
                  Central Telefónica
                </span>
              </div>
              <p class="font-body-sm text-body-sm text-on-surface-variant">
                Atención directa:
                <a class="font-bold text-primary hover:underline" href="tel:0987973511">
                  098 797 3511
                </a>
                en Pasaje, El Oro.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>
