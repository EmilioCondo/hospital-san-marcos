<?php
declare(strict_types=1);

if (!defined('HSM_APP')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

$breadcrumbLabel = $currentPage ?? ($pageMeta['breadcrumb'] ?? '');
?>
<nav
  aria-label="Ruta de navegación"
  class="flex items-center gap-2 text-sm font-medium text-on-surface-variant mb-6 min-h-[24px]"
>
  <a
    href="<?= e(url('/')) ?>"
    class="inline-flex items-center gap-2 hover:text-primary transition-colors"
  >
    <span class="material-symbols-outlined text-[18px] leading-none">
      home
    </span>
    <span>Inicio</span>
  </a>

  <span class="material-symbols-outlined text-[18px] leading-none text-outline">
    chevron_right
  </span>

  <span class="text-primary font-semibold">
    <?= e($breadcrumbLabel) ?>
  </span>
</nav>
