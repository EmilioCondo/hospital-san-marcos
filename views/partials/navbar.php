<?php
declare(strict_types=1);

if (!defined('HSM_APP')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

$navLinks = [
    ['name' => 'Inicio',         'path' => '/'],
    ['name' => 'Nosotros',       'path' => '/nosotros'],
    ['name' => 'Servicios',      'path' => '/servicios'],
    ['name' => 'Especialidades', 'path' => '/especialidades'],
    ['name' => 'Equipo médico',  'path' => '/equipo-medico'],
    ['name' => 'Contacto',       'path' => '/contacto'],
];
?>
<header class="fixed top-0 left-0 right-0 z-50 bg-surface">
  <!-- Top Utility Contact Bar -->
  <div id="top-utility-bar" class="top-utility-bar bg-primary-container text-white py-1.5 px-gutter">
    <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-space-sm font-label-sm text-label-sm">
      <div class="flex items-center gap-space-md">
        <div class="flex items-center gap-1.5 text-surface-container-highest">
  <span class="material-symbols-outlined text-[16px]">location_on</span>
  <a
    class="hover:text-surface transition-colors"
    href="https://maps.app.goo.gl/wSFw1YrfLKJfjsxN6"
    target="_blank"
    rel="noopener noreferrer"
  >
    Pasaje, El Oro, Ecuador
  </a>
</div>
        <div class="hidden sm:flex items-center gap-1.5 text-surface-container-highest">
          <span class="material-symbols-outlined text-[16px]">call</span>
          <a class="hover:text-surface transition-colors" href="tel:0987973511">
            098 797 3511
          </a>
        </div>
      </div>
      <div class="flex items-center gap-space-md">
        <div class="flex items-center gap-1.5 bg-error-container text-on-error-container px-2.5 py-0.5 rounded-full">
          <span class="font-label-sm text-label-sm font-bold uppercase tracking-wider">
            Emergencias 24 Horas
          </span>
        </div>
        <a
          class="hidden md:flex items-center gap-1 text-secondary-fixed hover:text-surface transition-colors font-medium"
          href="https://wa.me/593987973511"
          rel="noopener noreferrer"
          target="_blank"
        >
          <span class="material-symbols-outlined text-[16px]">chat</span>
          <span>WhatsApp Directo</span>
        </a>
      </div>
    </div>
  </div>

  <!-- Main Navigation Bar -->
  <div class="bg-surface-container-lowest/95 backdrop-blur-md shadow-[0_1px_8px_rgba(0,0,0,0.06)] border-b border-outline-variant">
    <div class="h-20 max-w-7xl mx-auto px-gutter flex items-center justify-between">
<!-- Logo & Brand Name -->
      <a
        href="<?= e(url('/')) ?>"
        class="flex items-center group shrink-0"
      >
        <img
          src="<?= e(url('/img/Logo.png')) ?>"
          alt="Hospital San Marcos"
          class="transition-transform group-hover:scale-105"
          style="height: 72px; width: auto; max-width: min(280px, 60vw); object-fit: contain;"
        >
      </a>

      <!-- Desktop Nav Links -->
      <nav class="hidden xl:flex items-center gap-space-lg">
        <?php foreach ($navLinks as $link): ?>
          <?php
          $isActive = ($link['path'] === '/')
              ? ($currentRoute === '/')
              : str_starts_with($currentRoute, $link['path']);
          ?>
          <a
            href="<?= e(url($link['path'])) ?>"
            class="font-title-md text-label-md transition-colors <?= $isActive
                ? 'text-primary font-bold border-b-2 border-primary py-1'
                : 'text-on-surface-variant hover:text-primary font-medium' ?>"
          >
            <?= e($link['name']) ?>
          </a>
        <?php endforeach; ?>
      </nav>

      <!-- Action CTAs & Mobile Burger Trigger -->
      <div class="flex items-center gap-space-md">
        <a
          href="<?= e(url('/agendar-cita')) ?>"
          class="hidden sm:inline-flex items-center justify-center bg-primary-container text-on-primary font-title-md text-label-md font-semibold px-space-lg py-2.5 rounded-full hover:bg-primary transition-all duration-200 shadow-sm hover:shadow"
        >
          Agendar cita
        </a>
        <a
          href="<?= e(url('/admin')) ?>"
          aria-label="Panel administrativo"
          title="Panel administrativo"
          class="w-8 h-8 rounded-full bg-primary hover:bg-on-primary-container transition-colors focus-primary flex items-center justify-center shrink-0"
        >
          <span class="material-symbols-outlined text-on-primary text-[18px]">person</span>
        </a>

        <!-- Mobile Hamburger Button -->
        <button
          type="button"
          id="mobile-menu-toggle"
          aria-expanded="false"
          aria-controls="mobile-menu-dropdown"
          class="xl:hidden p-2 rounded-xl text-on-surface hover:bg-surface-container transition-colors"
          aria-label="Abrir menú de navegación"
        >
          <span id="mobile-menu-icon" class="material-symbols-outlined text-[26px]">menu</span>
        </button>
      </div>
    </div>

    <!-- Mobile Slide-out / Dropdown Menu -->
    <div
      id="mobile-menu-dropdown"
      class="hidden xl:hidden bg-surface-container-lowest border-t border-outline-variant px-gutter py-space-md shadow-lg transition-all animate-fadeIn"
    >
      <nav class="flex flex-col space-y-2">
        <?php foreach ($navLinks as $link): ?>
          <?php
          $isActive = ($link['path'] === '/')
              ? ($currentRoute === '/')
              : str_starts_with($currentRoute, $link['path']);
          ?>
          <a
            href="<?= e(url($link['path'])) ?>"
            class="px-4 py-3 rounded-xl font-title-md text-body-md transition-colors flex items-center justify-between <?= $isActive
                ? 'bg-surface-container-low text-primary font-bold'
                : 'text-on-surface hover:bg-surface-container' ?>"
          >
            <span><?= e($link['name']) ?></span>
            <?php if ($isActive): ?>
              <span class="material-symbols-outlined text-[18px] text-primary">
                check
              </span>
            <?php endif; ?>
          </a>
        <?php endforeach; ?>
        <div class="pt-space-sm border-t border-outline-variant space-y-2">
          <a
            href="<?= e(url('/agendar-cita')) ?>"
            class="w-full inline-flex items-center justify-center gap-2 bg-primary-container text-on-primary font-title-md text-body-md font-semibold px-6 py-3 rounded-xl hover:bg-primary transition-colors text-center"
          >
            <span class="material-symbols-outlined text-[20px]">calendar_today</span>
            <span>Agendar cita médica</span>
          </a>
          <a
            href="tel:0987973511"
            class="w-full inline-flex items-center justify-center gap-2 bg-error-container text-on-error-container font-title-md text-body-md font-semibold px-6 py-3 rounded-xl hover:bg-error hover:text-on-error transition-colors text-center"
          >
            <span class="material-symbols-outlined text-[20px]">emergency</span>
            <span>Emergencias: 098 797 3511</span>
          </a>
        </div>
      </nav>
    </div>
  </div>
</header>
