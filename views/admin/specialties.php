<?php
declare(strict_types=1);

if (!defined('HSM_APP')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

$isEdit = is_array($editing) && !empty($editing['id']);
$selectedCats = is_array($editing['category_ids'] ?? null)
    ? array_map('intval', $editing['category_ids'])
    : [];
?>
<div class="space-y-6 animate-fadeIn">
  <div class="flex flex-wrap items-center justify-between gap-3">
    <div>
      <span class="font-label-sm text-label-sm text-primary uppercase tracking-wider font-bold block">
        Catálogo Institucional
      </span>
      <h1 class="font-headline-md text-headline-md text-on-surface font-semibold">
        Especialidades Médicas (<?= count($specialties) ?>)
      </h1>
    </div>
    <?php if (!$showForm): ?>
      <a
        href="<?= e(url('/admin/especialidades') . '?new=1') ?>"
        class="inline-flex items-center gap-2 bg-primary text-on-primary font-title-md text-label-md font-bold px-5 py-2.5 rounded-full hover:bg-on-primary-container transition-colors shadow-sm focus-primary"
      >
        <span class="material-symbols-outlined text-[18px]">add_circle</span>
        <span>Nueva especialidad</span>
      </a>
    <?php endif; ?>
  </div>

  <?php if ($showForm): ?>
    <div class="bg-surface-container-lowest p-6 rounded-2xl shadow-sm border border-outline-variant space-y-5">
      <div class="flex items-center justify-between border-b border-surface-container-high pb-3">
        <h2 class="font-headline-sm text-title-lg font-bold text-on-surface">
          <?= $isEdit ? 'Editar especialidad: ' . e((string) $editing['name']) : 'Nueva especialidad médica' ?>
        </h2>
        <a href="<?= e(url('/admin/especialidades')) ?>" class="font-label-md text-label-md text-outline hover:text-on-surface">
          Cancelar
        </a>
      </div>

      <?php if ($formErrors !== []): ?>
        <div role="alert" class="p-4 rounded-xl bg-error-container text-on-error-container border border-outline-variant space-y-1">
          <p class="font-title-md text-body-sm font-bold">Corrige los siguientes campos:</p>
          <ul class="font-body-sm text-body-sm pl-5">
            <?php foreach ($formErrors as $err): ?>
              <li>• <?= e($err) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="post" action="<?= e(url('/admin/especialidades')) ?>" class="space-y-5">
        <input type="hidden" name="_csrf" value="<?= e(Security::csrfToken()) ?>" />
        <input type="hidden" name="_action" value="save" />
        <input type="hidden" name="id" value="<?= $isEdit ? (int) $editing['id'] : 0 ?>" />

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="space-y-1.5">
            <label for="spec-name" class="block font-title-md text-label-md font-semibold text-on-surface">
              Nombre de la especialidad <span class="text-tertiary">*</span>
            </label>
            <input
              id="spec-name"
              name="name"
              type="text"
              required
              value="<?= e((string) ($editing['name'] ?? '')) ?>"
              placeholder="Ej. Cardiología"
              class="w-full bg-surface-container-low text-on-surface px-4 py-2.5 rounded-xl font-body-sm text-body-sm border border-outline-variant focus-primary"
            />
          </div>

          <div class="space-y-1.5">
            <label for="spec-badge" class="block font-title-md text-label-md font-semibold text-on-surface">
              Insignia corta (Badge) <span class="text-tertiary">*</span>
            </label>
            <input
              id="spec-badge"
              name="badge"
              type="text"
              required
              value="<?= e((string) ($editing['badge'] ?? '')) ?>"
              placeholder="Ej. Corazón / Adultos"
              class="w-full bg-surface-container-low text-on-surface px-4 py-2.5 rounded-xl font-body-sm text-body-sm border border-outline-variant focus-primary"
            />
          </div>

          <div class="space-y-1.5">
            <label for="spec-badge-type" class="block font-title-md text-label-md font-semibold text-on-surface">
              Estilo de insignia
            </label>
            <select
              id="spec-badge-type"
              name="badge_type"
              class="w-full bg-surface-container-low text-on-surface px-4 py-2.5 rounded-xl font-body-sm text-body-sm border border-outline-variant focus-primary"
            >
              <option value="" <?= empty($editing['badge_type']) ? 'selected' : '' ?>>Estándar institucional</option>
              <option value="highlight" <?= (($editing['badge_type'] ?? '') === 'highlight') ? 'selected' : '' ?>>Destacado (Verde)</option>
              <option value="pediatria" <?= (($editing['badge_type'] ?? '') === 'pediatria') ? 'selected' : '' ?>>Pediátrico (Verde)</option>
            </select>
          </div>

          <div class="space-y-1.5">
            <label for="spec-icon" class="block font-title-md text-label-md font-semibold text-on-surface">
              Ícono Material Symbols <span class="text-tertiary">*</span>
            </label>
            <input
              id="spec-icon"
              name="icon"
              type="text"
              required
              value="<?= e((string) ($editing['icon'] ?? 'stethoscope')) ?>"
              placeholder="Ej. stethoscope, cardiology"
              class="w-full bg-surface-container-low text-on-surface px-4 py-2.5 rounded-xl font-body-sm text-body-sm border border-outline-variant focus-primary"
            />
          </div>

          <div class="space-y-1.5">
            <label for="spec-highlight" class="block font-title-md text-label-md font-semibold text-on-surface">
              Nota destacada opcional
            </label>
            <input
              id="spec-highlight"
              name="highlight_text"
              type="text"
              value="<?= e((string) ($editing['highlight_text'] ?? '')) ?>"
              placeholder="Opcional: aparece en recuadro inferior"
              class="w-full bg-surface-container-low text-on-surface px-4 py-2.5 rounded-xl font-body-sm text-body-sm border border-outline-variant focus-primary"
            />
          </div>

          <div class="grid grid-cols-2 gap-2">
            <div class="space-y-1.5">
              <label for="spec-order" class="block font-title-md text-label-md font-semibold text-on-surface">
                Orden
              </label>
              <input
                id="spec-order"
                name="sort_order"
                type="number"
                min="1"
                value="<?= (int) ($editing['sort_order'] ?? (count($specialties) + 1)) ?>"
                class="w-full bg-surface-container-low text-on-surface px-3 py-2.5 rounded-xl font-body-sm text-body-sm border border-outline-variant focus-primary"
              />
            </div>
            <div class="space-y-1.5">
              <label for="spec-slug" class="block font-title-md text-label-md font-semibold text-on-surface">
                Slug
              </label>
              <input
                id="spec-slug"
                name="slug"
                type="text"
                placeholder="Auto"
                value="<?= e((string) ($editing['slug'] ?? '')) ?>"
                class="w-full bg-surface-container-low text-on-surface px-3 py-2.5 rounded-xl font-body-sm text-body-sm border border-outline-variant focus-primary"
              />
            </div>
          </div>
        </div>

        <div class="space-y-1.5">
          <label for="spec-desc" class="block font-title-md text-label-md font-semibold text-on-surface">
            Descripción clínica <span class="text-tertiary">*</span>
          </label>
          <textarea
            id="spec-desc"
            name="description"
            rows="2"
            required
            class="w-full bg-surface-container-low text-on-surface p-3.5 rounded-xl font-body-sm text-body-sm border border-outline-variant focus-primary resize-none"
          ><?= e((string) ($editing['description'] ?? '')) ?></textarea>
        </div>

        <div class="space-y-2">
          <span class="block font-title-md text-label-md font-semibold text-on-surface">
            Categorías de filtrado en la página Especialidades <span class="text-tertiary">*</span>
          </span>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 p-4 rounded-xl bg-surface-container-low border border-outline-variant">
            <?php foreach ($categories as $cat): ?>
              <?php $cid = (int) $cat['id']; ?>
              <label class="flex items-center gap-2 cursor-pointer font-body-sm text-body-sm text-on-surface">
                <input
                  type="checkbox"
                  name="category_ids[]"
                  value="<?= $cid ?>"
                  <?= in_array($cid, $selectedCats, true) ? 'checked' : '' ?>
                  class="w-4 h-4 accent-primary rounded"
                />
                <span><?= e((string) $cat['label']) ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="pt-2 flex items-center gap-3">
          <button
            type="submit"
            class="inline-flex items-center gap-2 bg-primary text-on-primary hover:bg-on-primary-container font-title-md text-label-md font-bold px-6 py-3 rounded-full cursor-pointer shadow-sm focus-primary"
          >
            <span class="material-symbols-outlined text-[18px]">save</span>
            <span><?= $isEdit ? 'Guardar especialidad' : 'Crear especialidad' ?></span>
          </button>
          <a
            href="<?= e(url('/admin/especialidades')) ?>"
            class="inline-flex items-center px-5 py-3 rounded-full bg-surface-container-low text-on-surface-variant hover:bg-surface-container font-label-md text-label-md border border-outline-variant"
          >
            Cancelar
          </a>
        </div>
      </form>
    </div>
  <?php endif; ?>

  <div class="bg-surface-container-lowest p-5 rounded-2xl shadow-sm border border-outline-variant overflow-x-auto">
    <table class="w-full text-left font-body-sm text-body-sm">
      <thead>
        <tr class="border-b border-outline-variant text-outline font-label-sm text-label-sm uppercase">
          <th class="py-3 pr-3">#</th>
          <th class="py-3 pr-4">Especialidad</th>
          <th class="py-3 pr-4">Grupos de filtro</th>
          <th class="py-3 pr-4">Médicos vinculados</th>
          <th class="py-3">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($specialties as $sp): ?>
          <tr class="border-b border-surface-container-high">
            <td class="py-3 pr-3 text-outline"><?= (int) $sp['sort_order'] ?></td>
            <td class="py-3 pr-4">
              <div class="flex items-center gap-2.5">
                <span class="material-symbols-outlined text-primary text-[20px]"><?= e((string) $sp['icon']) ?></span>
                <div>
                  <span class="font-bold text-on-surface block"><?= e((string) $sp['name']) ?></span>
                  <span class="text-outline text-label-sm">Insignia: <?= e((string) $sp['badge']) ?> • <?= e((string) $sp['slug']) ?></span>
                </div>
              </div>
            </td>
            <td class="py-3 pr-4 text-on-surface-variant">
              <?= e((string) ($sp['categories_label'] ?? '')) ?>
            </td>
            <td class="py-3 pr-4 font-semibold text-primary">
              <?= (int) $sp['doctors_count'] ?>
            </td>
            <td class="py-3 whitespace-nowrap">
              <div class="flex items-center gap-2">
                <a
                  href="<?= e(url('/admin/especialidades') . '?edit=' . (int) $sp['id']) ?>"
                  class="px-3 py-1.5 rounded-full bg-surface-container-low hover:bg-primary hover:text-on-primary text-primary font-label-sm text-label-sm font-bold border border-outline-variant"
                >
                  Editar
                </a>
                <form method="post" action="<?= e(url('/admin/especialidades')) ?>" onsubmit="return confirm('¿Eliminar esta especialidad? Los médicos vinculados quedarán sin esta relación.');">
                  <input type="hidden" name="_csrf" value="<?= e(Security::csrfToken()) ?>" />
                  <input type="hidden" name="_action" value="delete" />
                  <input type="hidden" name="id" value="<?= (int) $sp['id'] ?>" />
                  <button
                    type="submit"
                    class="px-3 py-1.5 rounded-full bg-error-container text-on-error-container font-label-sm text-label-sm font-bold cursor-pointer"
                  >
                    Eliminar
                  </button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
