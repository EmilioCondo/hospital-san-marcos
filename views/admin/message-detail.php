<?php
declare(strict_types=1);

if (!defined('HSM_APP')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

$st = (string) $message['status'];
$badgeCls = match ($st) {
    'nuevo'      => 'bg-primary text-on-primary',
    'respondido' => 'bg-secondary-container text-on-secondary-container',
    default      => 'bg-surface-container-high text-on-surface',
};
$mailtoSubject = rawurlencode('Re: [' . $message['code'] . '] ' . $message['subject_label'] . ' — Hospital San Marcos');
?>
<div class="space-y-6 animate-fadeIn">
  <div class="flex flex-wrap items-center justify-between gap-3">
    <div class="space-y-1">
      <a href="<?= e(url('/admin/mensajes')) ?>" class="inline-flex items-center gap-1 font-label-md text-label-md text-primary hover:underline">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span>
        <span>Volver a la bandeja de mensajes</span>
      </a>
      <h1 class="font-headline-md text-headline-md text-on-surface font-semibold">
        Consulta <?= e((string) $message['code']) ?>
      </h1>
    </div>
    <span class="inline-flex px-4 py-1.5 rounded-full font-label-md text-label-md font-bold uppercase <?= $badgeCls ?>">
      Estado: <?= e($st) ?>
    </span>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    <div class="lg:col-span-8 bg-surface-container-lowest p-6 rounded-2xl shadow-sm border border-outline-variant space-y-5">
      <div class="border-b border-surface-container-high pb-4 space-y-1">
        <span class="font-label-sm text-label-sm uppercase text-primary font-bold block">
          <?= e((string) $message['subject_label']) ?>
        </span>
        <h2 class="font-headline-sm text-title-lg font-bold text-on-surface">
          Remitente: <?= e((string) $message['name']) ?>
        </h2>
        <p class="font-body-sm text-body-sm text-on-surface-variant">
          Correo: <a href="mailto:<?= e((string) $message['email']) ?>?subject=<?= e($mailtoSubject) ?>" class="link-primary"><?= e((string) $message['email']) ?></a>
          <?php if (!empty($message['phone'])): ?>
            • Teléfono: <a href="tel:<?= e(preg_replace('/\D+/', '', (string) $message['phone']) ?? '') ?>" class="link-primary"><?= e((string) $message['phone']) ?></a>
          <?php endif; ?>
        </p>
      </div>

      <div class="p-5 rounded-xl bg-surface-container-low border border-outline-variant">
        <p class="font-body-md text-body-md text-on-surface leading-relaxed">
          <?= nl2br(e((string) $message['message']), false) ?>
        </p>
      </div>

      <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
        <span class="font-label-sm text-label-sm text-outline">
          Recibido el: <?= e((string) $message['created_at']) ?>
        </span>
        <a
          href="mailto:<?= e((string) $message['email']) ?>?subject=<?= e($mailtoSubject) ?>"
          class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-surface-container-lowest text-primary hover:bg-surface-container font-title-md text-label-md font-bold border border-outline-variant focus-primary"
        >
          <span class="material-symbols-outlined text-[18px]">reply</span>
          <span>Responder por correo</span>
        </a>
      </div>
    </div>

    <div class="lg:col-span-4 bg-surface-container-lowest p-6 rounded-2xl shadow-sm border border-outline-variant space-y-4">
      <h2 class="font-headline-sm text-title-lg font-bold text-on-surface border-b border-surface-container-high pb-3">
        Actualizar estado
      </h2>

      <form method="post" action="<?= e(url('/admin/mensajes/ver') . '?id=' . (int) $message['id']) ?>" class="space-y-4">
        <input type="hidden" name="_csrf" value="<?= e(Security::csrfToken()) ?>" />
        <input type="hidden" name="id" value="<?= (int) $message['id'] ?>" />

        <div class="space-y-2">
          <label for="msg-status" class="block font-title-md text-label-md font-semibold text-on-surface">
            Estado de seguimiento
          </label>
          <select
            id="msg-status"
            name="status"
            class="w-full bg-surface-container-low text-on-surface px-4 py-3 rounded-xl font-body-md text-body-md border border-outline-variant focus-primary"
          >
            <option value="nuevo" <?= $st === 'nuevo' ? 'selected' : '' ?>>Nuevo (Sin leer)</option>
            <option value="leido" <?= $st === 'leido' ? 'selected' : '' ?>>Leído (En revisión)</option>
            <option value="respondido" <?= $st === 'respondido' ? 'selected' : '' ?>>Respondido / Atendido</option>
          </select>
        </div>

        <button
          type="submit"
          class="w-full inline-flex items-center justify-center gap-2 bg-primary text-on-primary hover:bg-on-primary-container transition-colors font-title-md text-body-md font-bold px-6 py-3.5 rounded-full cursor-pointer shadow-sm focus-primary"
        >
          <span class="material-symbols-outlined text-[20px]">save</span>
          <span>Guardar estado</span>
        </button>
      </form>
    </div>
  </div>
</div>
