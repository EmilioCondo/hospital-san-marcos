<?php
declare(strict_types=1);

if (!defined('HSM_APP')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}
?>
<!doctype html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="robots" content="noindex, nofollow" />
    <title><?= e($pageTitle ?? 'Acceso Administrativo | Hospital San Marcos') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@500;700&family=Newsreader:ital,opsz,wght@0,6..72,400..700;1,6..72,400..700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    <link rel="stylesheet" href="<?= e(asset('css/styles.css')) ?>" />
  </head>
  <body class="bg-surface text-on-surface font-body-md min-h-screen flex flex-col">
    <!-- Top Institutional Bar -->
    <div class="bg-primary-container text-white py-2 px-gutter">
      <div class="max-w-7xl mx-auto flex items-center justify-between font-label-sm text-label-sm">
        <span>Hospital San Marcos • Gestión Interna</span>
        <a href="<?= e(url('/')) ?>" class="inline-flex items-center gap-1.5 text-white hover:underline focus-primary">
          <span class="material-symbols-outlined text-[16px]">arrow_back</span>
          <span>Volver al portal público</span>
        </a>
      </div>
    </div>

    <main class="flex-1 flex items-center justify-center px-gutter py-12">
      <div class="w-full max-w-md bg-surface-container-lowest p-6 sm:p-10 rounded-2xl shadow-md border border-outline-variant space-y-6 animate-fadeIn">
        <!-- Brand Header -->
        <div class="text-center space-y-3">
          <div class="w-12 h-12 rounded-2xl bg-primary text-on-primary flex items-center justify-center mx-auto shadow-sm">
            <span class="material-symbols-outlined text-[26px]">admin_panel_settings</span>
          </div>
          <div>
            <span class="font-label-sm text-label-sm text-primary uppercase tracking-wider font-bold block">
              Acceso Restringido
            </span>
            <h1 class="font-headline-sm text-headline-sm text-on-surface font-semibold mt-1">
              Panel Administrativo
            </h1>
            <p class="font-body-sm text-body-sm text-on-surface-variant mt-1">
              Admisión, citas médicas y consultas institucionales.
            </p>
          </div>
        </div>

        <?php if (!empty($flashNotice['message'])): ?>
          <div role="status" class="p-4 rounded-xl bg-surface-container-high text-on-surface border border-outline-variant font-body-sm text-body-sm flex items-start gap-2.5">
            <span class="material-symbols-outlined text-primary text-[20px] shrink-0">info</span>
            <span><?= e((string) $flashNotice['message']) ?></span>
          </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
          <div id="admin-login-error" role="alert" class="p-4 rounded-xl bg-error-container text-on-error-container border border-outline-variant font-body-sm text-body-sm flex items-start gap-2.5">
            <span class="material-symbols-outlined text-[20px] shrink-0">error</span>
            <span><?= e((string) $error) ?></span>
          </div>
        <?php endif; ?>

        <?php if (!$hasAnyAdmins): ?>
          <div class="p-4 rounded-xl bg-surface-container-low border border-outline-variant space-y-1">
            <p class="font-label-md text-label-md font-bold text-primary">
              Configuración inicial pendiente
            </p>
            <p class="font-body-sm text-body-sm text-on-surface-variant leading-snug">
              Aún no existe ningún usuario administrativo registrado. Crea tu cuenta inicial ejecutando en consola:
              <code>php database/create_admin.php</code>
            </p>
          </div>
        <?php endif; ?>

        <form method="post" action="<?= e(url('/admin/login')) ?>" class="space-y-5" novalidate>
          <input type="hidden" name="_csrf" value="<?= e(Security::csrfToken()) ?>" />

          <div class="space-y-2">
            <label class="block font-title-md text-label-md font-semibold text-on-surface" for="admin-username">
              Usuario o correo electrónico
            </label>
            <div class="relative">
              <input
                id="admin-username"
                name="username"
                type="text"
                autocomplete="username"
                required
                autofocus
                value="<?= e($oldUsername) ?>"
                placeholder="Ej. admin"
                class="w-full bg-surface-container-low text-on-surface placeholder:text-outline px-4 py-3.5 pl-11 rounded-xl font-body-md text-body-md focus-primary border border-outline-variant"
              />
              <span class="material-symbols-outlined absolute left-3.5 top-3.5 text-outline text-[20px]">
                person
              </span>
            </div>
          </div>

          <div class="space-y-2">
            <label class="block font-title-md text-label-md font-semibold text-on-surface" for="admin-password">
              Contraseña
            </label>
            <div class="relative">
              <input
                id="admin-password"
                name="password"
                type="password"
                autocomplete="current-password"
                required
                placeholder="••••••••"
                class="w-full bg-surface-container-low text-on-surface placeholder:text-outline px-4 py-3.5 pl-11 rounded-xl font-body-md text-body-md focus-primary border border-outline-variant"
              />
              <span class="material-symbols-outlined absolute left-3.5 top-3.5 text-outline text-[20px]">
                lock
              </span>
            </div>
          </div>

          <button
            type="submit"
            class="w-full inline-flex items-center justify-center gap-2 bg-primary text-on-primary hover:bg-on-primary-container transition-all shadow-sm font-title-md text-body-md font-bold px-6 py-3.5 rounded-full cursor-pointer focus-primary"
          >
            <span class="material-symbols-outlined text-[20px]">login</span>
            <span>Iniciar sesión</span>
          </button>
        </form>
      </div>
    </main>
  </body>
</html>
