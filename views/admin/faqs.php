<?php
declare(strict_types=1);

if (!defined('HSM_APP')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

$isEdit = is_array($editing) && !empty($editing['id']);
?>
<div class="space-y-6 animate-fadeIn">
  <div class="flex flex-wrap items-center justify-between gap-3">
    <div>
      <span class="font-label-sm text-label-sm text-primary uppercase tracking-wider font-bold block">
        Orientación al Paciente
      </span>
      <h1 class="font-headline-md text-headline-md text-on-surface font-semibold">
        Preguntas Frecuentes — FAQs (<?= count($faqs) ?>)
      </h1>
    </div>
    <?php if (!$showForm): ?>
      <a
        href="<?= e(url('/admin/faqs') . '?new=1') ?>"
        class="inline-flex items-center gap-2 bg-primary text-on-primary font-title-md text-label-md font-bold px-5 py-2.5 rounded-full hover:bg-on-primary-container transition-colors shadow-sm focus-primary"
      >
        <span class="material-symbols-outlined text-[18px]">add_circle</span>
        <span>Nueva pregunta</span>
      </a>
    <?php endif; ?>
  </div>

  <?php if ($showForm): ?>
    <div class="bg-surface-container-lowest p-6 rounded-2xl shadow-sm border border-outline-variant space-y-5">
      <div class="flex items-center justify-between border-b border-surface-container-high pb-3">
        <h2 class="font-headline-sm text-title-lg font-bold text-on-surface">
          <?= $isEdit ? 'Editar pregunta frecuente' : 'Nueva pregunta frecuente' ?>
        </h2>
        <a href="<?= e(url('/admin/faqs')) ?>" class="font-label-md text-label-md text-outline hover:text-on-surface">
          Cancelar
        </a>
      </div>

      <?php if ($formErrors !== []): ?>
        <div role="alert" class="p-4 rounded-xl bg-error-container text-on-error-container border border-outline-variant space-y-1">
          <ul class="font-body-sm text-body-sm pl-5">
            <?php foreach ($formErrors as $err): ?>
              <li>• <?= e($err) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="post" action="<?= e(url('/admin/faqs')) ?>" class="space-y-4">
        <input type="hidden" name="_csrf" value="<?= e(Security::csrfToken()) ?>" />
        <input type="hidden" name="_action" value="save" />
        <input type="hidden" name="id" value="<?= $isEdit ? (int) $editing['id'] : 0 ?>" />

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div class="md:col-span-2 space-y-1.5">
            <label for="faq-q" class="block font-title-md text-label-md font-semibold text-on-surface">
              Pregunta <span class="text-tertiary">*</span>
            </label>
            <input
              id="faq-q"
              name="question"
              type="text"
              required
              value="<?= e((string) ($editing['question'] ?? '')) ?>"
              class="w-full bg-surface-container-low text-on-surface px-4 py-2.5 rounded-xl font-body-sm text-body-sm border border-outline-variant focus-primary"
            />
          </div>

          <div class="space-y-1.5">
            <label for="faq-order" class="block font-title-md text-label-md font-semibold text-on-surface">
              Orden
            </label>
            <input
              id="faq-order"
              name="sort_order"
              type="number"
              min="1"
              value="<?= (int) ($editing['sort_order'] ?? (count($faqs) + 1)) ?>"
              class="w-full bg-surface-container-low text-on-surface px-4 py-2.5 rounded-xl font-body-sm text-body-sm border border-outline-variant focus-primary"
            />
          </div>

          <div class="space-y-1.5">
            <label for="faq-slug" class="block font-title-md text-label-md font-semibold text-on-surface">
              Slug
            </label>
            <input
              id="faq-slug"
              name="slug"
              type="text"
              placeholder="Auto"
              value="<?= e((string) ($editing['slug'] ?? '')) ?>"
              class="w-full bg-surface-container-low text-on-surface px-4 py-2.5 rounded-xl font-body-sm text-body-sm border border-outline-variant focus-primary"
            />
          </div>
        </div>

        <div class="space-y-1.5">
          <label for="faq-a" class="block font-title-md text-label-md font-semibold text-on-surface">
            Respuesta institucional <span class="text-tertiary">*</span>
          </label>
          <textarea
            id="faq-a"
            name="answer"
            rows="4"
            required
            class="w-full bg-surface-container-low text-on-surface p-3.5 rounded-xl font-body-sm text-body-sm border border-outline-variant focus-primary resize-none"
          ><?= e((string) ($editing['answer'] ?? '')) ?></textarea>
        </div>

        <label class="flex items-center gap-2 cursor-pointer font-body-sm text-body-sm text-on-surface">
          <input
            type="checkbox"
            name="is_active"
            value="1"
            <?= (!isset($editing['is_active']) || (bool) $editing['is_active']) ? 'checked' : '' ?>
            class="w-4 h-4 accent-primary rounded"
          />
          <span>Publicada en la página de Servicios</span>
        </label>

        <div class="pt-2 flex items-center gap-3">
          <button
            type="submit"
            class="inline-flex items-center gap-2 bg-primary text-on-primary hover:bg-on-primary-container font-title-md text-label-md font-bold px-6 py-3 rounded-full cursor-pointer shadow-sm focus-primary"
          >
            <span class="material-symbols-outlined text-[18px]">save</span>
            <span><?= $isEdit ? 'Guardar cambios' : 'Crear pregunta' ?></span>
          </button>
          <a href="<?= e(url('/admin/faqs')) ?>" class="px-5 py-3 rounded-full bg-surface-container-low text-on-surface-variant border border-outline-variant font-label-md text-label-md">
            Cancelar
          </a>
        </div>
      </form>
    </div>
  <?php endif; ?>

  <div class="bg-surface-container-lowest p-5 rounded-2xl shadow-sm border border-outline-variant space-y-3">
    <?php foreach ($faqs as $faq): ?>
      <?php $active = (bool) $faq['is_active']; ?>
      <div class="p-4 rounded-xl bg-surface-container-low border border-outline-variant flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-1">
          <div class="flex items-center gap-2">
            <span class="font-label-sm text-label-sm text-outline">#<?= (int) $faq['sort_order'] ?></span>
            <span class="inline-flex px-2.5 py-0.5 rounded-full font-label-sm text-label-sm font-bold uppercase <?= $active ? 'bg-secondary-container text-on-secondary-container' : 'bg-surface-container-high text-outline' ?>">
              <?= $active ? 'Activa' : 'Oculta' ?>
            </span>
          </div>
          <h3 class="font-title-md text-body-md font-bold text-on-surface"><?= e((string) $faq['question']) ?></h3>
          <p class="font-body-sm text-body-sm text-on-surface-variant"><?= e((string) $faq['answer']) ?></p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
          <a
            href="<?= e(url('/admin/faqs') . '?edit=' . (int) $faq['id']) ?>"
            class="px-3 py-1.5 rounded-full bg-surface-container-lowest hover:bg-primary hover:text-on-primary text-primary font-label-sm text-label-sm font-bold border border-outline-variant"
          >
            Editar
          </a>
          <form method="post" action="<?= e(url('/admin/faqs')) ?>">
            <input type="hidden" name="_csrf" value="<?= e(Security::csrfToken()) ?>" />
            <input type="hidden" name="_action" value="toggle" />
            <input type="hidden" name="id" value="<?= (int) $faq['id'] ?>" />
            <button type="submit" class="px-3 py-1.5 rounded-full bg-surface-container-lowest hover:bg-surface-container text-on-surface-variant font-label-sm text-label-sm font-semibold border border-outline-variant cursor-pointer">
              <?= $active ? 'Ocultar' : 'Activar' ?>
            </button>
          </form>
          <form method="post" action="<?= e(url('/admin/faqs')) ?>" onsubmit="return confirm('¿Eliminar esta pregunta frecuente?');">
            <input type="hidden" name="_csrf" value="<?= e(Security::csrfToken()) ?>" />
            <input type="hidden" name="_action" value="delete" />
            <input type="hidden" name="id" value="<?= (int) $faq['id'] ?>" />
            <button type="submit" class="px-3 py-1.5 rounded-full bg-error-container text-on-error-container font-label-sm text-label-sm font-bold cursor-pointer">
              Eliminar
            </button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
