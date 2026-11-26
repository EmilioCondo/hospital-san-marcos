<?php
declare(strict_types=1);

if (!defined('HSM_APP')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

$canManageOp      = AdminAuth::hasRole($adminUser, ['admin', 'recepcion']);
$canManageCatalog = AdminAuth::hasRole($adminUser, ['admin', 'editor']);
$isSuperAdmin     = AdminAuth::hasRole($adminUser, ['admin']);

$navItems = [
    ['id' => 'dashboard',      'label' => 'Resumen',        'icon' => 'dashboard',               'path' => '/admin',                'show' => true],
    ['id' => 'citas',          'label' => 'Citas',          'icon' => 'calendar_month',          'path' => '/admin/citas',          'show' => $canManageOp],
    ['id' => 'mensajes',       'label' => 'Mensajes',       'icon' => 'mail',                    'path' => '/admin/mensajes',       'show' => $canManageOp],
    ['id' => 'medicos',        'label' => 'Médicos',        'icon' => 'stethoscope',             'path' => '/admin/medicos',        'show' => $canManageCatalog],
    ['id' => 'especialidades', 'label' => 'Especialidades', 'icon' => 'medical_services',        'path' => '/admin/especialidades', 'show' => $canManageCatalog],
    ['id' => 'servicios',      'label' => 'Servicios',      'icon' => 'local_hospital',          'path' => '/admin/servicios',      'show' => $canManageCatalog],
    ['id' => 'faqs',           'label' => 'FAQs',           'icon' => 'help',                    'path' => '/admin/faqs',           'show' => $canManageCatalog],
    ['id' => 'usuarios',       'label' => 'Usuarios',       'icon' => 'manage_accounts',         'path' => '/admin/usuarios',       'show' => $isSuperAdmin],
];
?>
<!doctype html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="robots" content="noindex, nofollow" />
    <title><?= e($pageTitle ?? 'Panel Administrativo | Hospital San Marcos') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@500;700&family=Newsreader:ital,opsz,wght@0,6..72,400..700;1,6..72,400..700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    <link rel="stylesheet" href="<?= e(asset('css/styles.css')) ?>" />
  </head>
  <body class="bg-surface text-on-surface font-body-md min-h-screen flex flex-col">
    <!-- Top Admin Header -->
    <header class="bg-surface-container-lowest border-b border-outline-variant shadow-sm">
      <div class="bg-primary text-on-primary py-1.5 px-gutter">
        <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-2 font-label-sm text-label-sm">
          <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-[16px]">verified_user</span>
            <span>Panel Interno — Hospital San Marcos (Pasaje, El Oro)</span>
          </div>
          <div class="flex items-center gap-4">
            <span>Sesión: <strong><?= e($adminUser['username']) ?></strong> (<?= e(AdminAuth::roleLabel($adminUser['role'])) ?>)</span>
            <a href="<?= e(url('/')) ?>" target="_blank" rel="noopener noreferrer" class="underline hover:text-primary-fixed">
              Ver sitio público
            </a>
          </div>
        </div>
      </div>

      <div class="max-w-7xl mx-auto px-gutter py-3 flex flex-wrap items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-2">
          <?php foreach ($navItems as $item): ?>
            <?php if (!$item['show']) continue; ?>
            <?php $isActive = ($activeMenu ?? '') === $item['id']; ?>
            <a
              href="<?= e(url($item['path'])) ?>"
              class="inline-flex items-center gap-2 px-4 py-2 rounded-full font-title-md text-label-md transition-colors focus-primary <?= $isActive
                  ? 'bg-primary text-on-primary font-bold shadow-sm'
                  : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container hover:text-on-surface font-semibold' ?>"
            >
              <span class="material-symbols-outlined text-[18px]"><?= e($item['icon']) ?></span>
              <span><?= e($item['label']) ?></span>
            </a>
          <?php endforeach; ?>
        </div>

        <form method="post" action="<?= e(url('/admin/logout')) ?>">
          <input type="hidden" name="_csrf" value="<?= e(Security::csrfToken()) ?>" />
          <button
            type="submit"
            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-surface-container-low hover:bg-error-container hover:text-on-error-container text-on-surface-variant font-title-md text-label-md font-semibold transition-colors border border-outline-variant cursor-pointer focus-primary"
          >
            <span class="material-symbols-outlined text-[18px]">logout</span>
            <span>Cerrar sesión</span>
          </button>
        </form>
      </div>
    </header>

    <!-- Main Content Container -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-gutter py-8 space-y-6">
      <?php if (!empty($adminFlash['message'])): ?>
        <?php $isErr = ($adminFlash['type'] ?? '') === 'error'; ?>
        <div
          role="<?= $isErr ? 'alert' : 'status' ?>"
          class="p-4 rounded-xl border border-outline-variant flex items-start gap-3 <?= $isErr
              ? 'bg-error-container text-on-error-container'
              : 'bg-secondary-container text-on-secondary-container' ?>"
        >
          <span class="material-symbols-outlined text-[20px] shrink-0">
            <?= $isErr ? 'error' : 'check_circle' ?>
          </span>
          <span class="font-body-sm text-body-sm font-semibold">
            <?= e((string) $adminFlash['message']) ?>
          </span>
        </div>
      <?php endif; ?>

      <?php require $adminViewFile; ?>
    </main>

    <footer class="border-t border-outline-variant bg-surface-container-lowest py-4 px-gutter mt-8">
      <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-2 font-label-sm text-label-sm text-outline">
        <span>© <?= date('Y') ?> Hospital San Marcos • Módulo Administrativo</span>
        <span>Uso exclusivo de personal autorizado</span>
      </div>
    </footer>
  </body>
</html>
