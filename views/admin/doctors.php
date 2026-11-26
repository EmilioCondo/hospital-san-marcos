<?php
declare(strict_types=1);

if (!defined('HSM_APP')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

$categoryOptions = [
    'cirugia'          => 'Cirugía y áreas quirúrgicas',
    'ginecologia'      => 'Ginecología',
    'medicina-interna' => 'Medicina interna y clínica',
    'traumatologia'    => 'Traumatología',
    'otras'            => 'Otras especialidades',
];

$isEdit = is_array($editing) && !empty($editing['id']);
$secSelected = is_array($editing['secondary_specialty_ids'] ?? null)
    ? array_map('intval', $editing['secondary_specialty_ids'])
    : [];
$primarySelected = isset($editing['specialty_id']) && $editing['specialty_id'] !== null
    ? (int) $editing['specialty_id']
    : 0;
?>
<div class="space-y-6 animate-fadeIn">
  <div class="flex flex-wrap items-center justify-between gap-3">
    <div>
      <span class="font-label-sm text-label-sm text-primary uppercase tracking-wider font-bold block">
        Catálogo Institucional
      </span>
      <h1 class="font-headline-md text-headline-md text-on-surface font-semibold">
        Directorio de Médicos (<?= count($doctors) ?>)
      </h1>
    </div>
    <?php if (!$showForm): ?>
      <a
        href="<?= e(url('/admin/medicos') . '?new=1') ?>"
        class="inline-flex items-center gap-2 bg-primary text-on-primary font-title-md text-label-md font-bold px-5 py-2.5 rounded-full hover:bg-on-primary-container transition-colors shadow-sm focus-primary"
      >
        <span class="material-symbols-outlined text-[18px]">person_add</span>
        <span>Nuevo médico</span>
      </a>
    <?php endif; ?>
  </div>

  <?php if ($showForm): ?>
    <div class="bg-surface-container-lowest p-6 rounded-2xl shadow-sm border border-outline-variant space-y-5">
      <div class="flex items-center justify-between border-b border-surface-container-high pb-3">
        <h2 class="font-headline-sm text-title-lg font-bold text-on-surface">
          <?= $isEdit ? 'Editar médico: ' . e((string) $editing['name']) : 'Registrar nuevo médico' ?>
        </h2>
        <a href="<?= e(url('/admin/medicos')) ?>" class="font-label-md text-label-md text-outline hover:text-on-surface">
          Cancelar
        </a>
      </div>

      <?php if ($formErrors !== []): ?>
        <div role="alert" class="p-4 rounded-xl bg-error-container text-on-error-container border border-outline-variant space-y-1">
          <p class="font-title-md text-body-sm font-bold">Revisa los siguientes campos:</p>
          <ul class="font-body-sm text-body-sm pl-5">
            <?php foreach ($formErrors as $err): ?>
              <li>• <?= e($err) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="post" action="<?= e(url('/admin/medicos')) ?>" class="space-y-5">
        <input type="hidden" name="_csrf" value="<?= e(Security::csrfToken()) ?>" />
        <input type="hidden" name="_action" value="save" />
        <input type="hidden" name="id" value="<?= $isEdit ? (int) $editing['id'] : 0 ?>" />

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="space-y-1.5">
            <label for="doc-name" class="block font-title-md text-label-md font-semibold text-on-surface">
              Nombre completo <span class="text-tertiary">*</span>
            </label>
            <input
              id="doc-name"
              name="name"
              type="text"
              required
              value="<?= e((string) ($editing['name'] ?? '')) ?>"
              placeholder="Ej. Johny Cabrera"
              class="w-full bg-surface-container-low text-on-surface px-4 py-2.5 rounded-xl font-body-sm text-body-sm border border-outline-variant focus-primary"
            />
          </div>

          <div class="space-y-1.5">
            <label for="doc-spec-label" class="block font-title-md text-label-md font-semibold text-on-surface">
              Título / Especialidad visible <span class="text-tertiary">*</span>
            </label>
            <input
              id="doc-spec-label"
              name="specialty_label"
              type="text"
              required
              value="<?= e((string) ($editing['specialty_label'] ?? '')) ?>"
              placeholder="Ej. Cirujano general y laparoscópico"
              class="w-full bg-surface-container-low text-on-surface px-4 py-2.5 rounded-xl font-body-sm text-body-sm border border-outline-variant focus-primary"
            />
          </div>

          <div class="space-y-1.5">
            <label for="doc-category" class="block font-title-md text-label-md font-semibold text-on-surface">
              Categoría en Equipo Médico <span class="text-tertiary">*</span>
            </label>
            <select
              id="doc-category"
              name="category"
              class="w-full bg-surface-container-low text-on-surface px-4 py-2.5 rounded-xl font-body-sm text-body-sm border border-outline-variant focus-primary"
            >
              <?php foreach ($categoryOptions as $catKey => $catLbl): ?>
                <option value="<?= e($catKey) ?>" <?= (($editing['category'] ?? 'medicina-interna') === $catKey) ? 'selected' : '' ?>>
                  <?= e($catLbl) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="space-y-1.5">
            <label for="doc-primary-spec" class="block font-title-md text-label-md font-semibold text-on-surface">
              Especialidad principal (Filtro de Citas)
            </label>
            <select
              id="doc-primary-spec"
              name="specialty_id"
              class="w-full bg-surface-container-low text-on-surface px-4 py-2.5 rounded-xl font-body-sm text-body-sm border border-outline-variant focus-primary"
            >
              <option value="0">— Sin especialidad en filtro (solo ?doctor=) —</option>
              <?php foreach ($specialties as $sp): ?>
                <option value="<?= (int) $sp['db_id'] ?>" <?= $primarySelected === (int) $sp['db_id'] ? 'selected' : '' ?>>
                  <?= e($sp['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="space-y-1.5">
            <label for="doc-location" class="block font-title-md text-label-md font-semibold text-on-surface">
              Ubicación / Modalidad
            </label>
            <input
              id="doc-location"
              name="location"
              type="text"
              value="<?= e((string) ($editing['location'] ?? 'Sede Pasaje · Consulta programada')) ?>"
              class="w-full bg-surface-container-low text-on-surface px-4 py-2.5 rounded-xl font-body-sm text-body-sm border border-outline-variant focus-primary"
            />
          </div>

          <div class="grid grid-cols-3 gap-2">
            <div class="space-y-1.5">
              <label for="doc-initials" class="block font-title-md text-label-md font-semibold text-on-surface">
                Iniciales
              </label>
              <input
                id="doc-initials"
                name="initials"
                type="text"
                maxlength="6"
                placeholder="Auto"
                value="<?= e((string) ($editing['initials'] ?? '')) ?>"
                class="w-full bg-surface-container-low text-on-surface px-3 py-2.5 rounded-xl font-body-sm text-body-sm border border-outline-variant focus-primary"
              />
            </div>
            <div class="space-y-1.5">
              <label for="doc-order" class="block font-title-md text-label-md font-semibold text-on-surface">
                Orden
              </label>
              <input
                id="doc-order"
                name="sort_order"
                type="number"
                min="1"
                value="<?= (int) ($editing['sort_order'] ?? (count($doctors) + 1)) ?>"
                class="w-full bg-surface-container-low text-on-surface px-3 py-2.5 rounded-xl font-body-sm text-body-sm border border-outline-variant focus-primary"
              />
            </div>
            <div class="space-y-1.5">
              <label for="doc-slug" class="block font-title-md text-label-md font-semibold text-on-surface">
                Slug
              </label>
              <input
                id="doc-slug"
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
          <label for="doc-desc" class="block font-title-md text-label-md font-semibold text-on-surface">
            Reseña profesional <span class="text-tertiary">*</span>
          </label>
          <textarea
            id="doc-desc"
            name="description"
            rows="3"
            required
            class="w-full bg-surface-container-low text-on-surface p-3.5 rounded-xl font-body-sm text-body-sm border border-outline-variant focus-primary resize-none"
          ><?= e((string) ($editing['description'] ?? '')) ?></textarea>
        </div>

        <div class="space-y-2">
          <span class="block font-title-md text-label-md font-semibold text-on-surface">
            Especialidades secundarias opcionales (para doble especialidad en Agendar Cita)
          </span>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 p-4 rounded-xl bg-surface-container-low border border-outline-variant">
            <?php foreach ($specialties as $sp): ?>
              <?php $spId = (int) $sp['db_id']; ?>
              <label class="flex items-center gap-2 cursor-pointer font-body-sm text-label-sm text-on-surface">
                <input
                  type="checkbox"
                  name="secondary_specialty_ids[]"
                  value="<?= $spId ?>"
                  <?= in_array($spId, $secSelected, true) ? 'checked' : '' ?>
                  class="w-4 h-4 accent-primary rounded"
                />
                <span><?= e($sp['name']) ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-6">
          <label class="flex items-center gap-2 cursor-pointer font-body-sm text-body-sm text-on-surface">
            <input
              type="checkbox"
              name="accredited"
              value="1"
              <?= (!isset($editing['accredited']) || (bool) $editing['accredited']) ? 'checked' : '' ?>
              class="w-4 h-4 accent-primary rounded"
            />
            <span>Profesional acreditado (desmarcar si está «Por confirmar»)</span>
          </label>

          <label class="flex items-center gap-2 cursor-pointer font-body-sm text-body-sm text-on-surface">
            <input
              type="checkbox"
              name="is_active"
              value="1"
              <?= (!isset($editing['is_active']) || (bool) $editing['is_active']) ? 'checked' : '' ?>
              class="w-4 h-4 accent-primary rounded"
            />
            <span>Visible en el portal público</span>
          </label>
        </div>

        <div class="pt-2 flex items-center gap-3">
          <button
            type="submit"
            class="inline-flex items-center gap-2 bg-primary text-on-primary hover:bg-on-primary-container font-title-md text-label-md font-bold px-6 py-3 rounded-full cursor-pointer shadow-sm focus-primary"
          >
            <span class="material-symbols-outlined text-[18px]">save</span>
            <span><?= $isEdit ? 'Guardar cambios' : 'Crear médico' ?></span>
          </button>
          <a
            href="<?= e(url('/admin/medicos')) ?>"
            class="inline-flex items-center px-5 py-3 rounded-full bg-surface-container-low text-on-surface-variant hover:bg-surface-container font-label-md text-label-md border border-outline-variant"
          >
            Cancelar
          </a>
        </div>
      </form>
    </div>
  <?php endif; ?>

  <!-- Tabla de Médicos -->
  <div class="bg-surface-container-lowest p-5 rounded-2xl shadow-sm border border-outline-variant overflow-x-auto">
    <table class="w-full text-left font-body-sm text-body-sm">
      <thead>
        <tr class="border-b border-outline-variant text-outline font-label-sm text-label-sm uppercase">
          <th class="py-3 pr-3">#</th>
          <th class="py-3 pr-4">Médico</th>
          <th class="py-3 pr-4">Título visible</th>
          <th class="py-3 pr-4">Especialidades (Citas)</th>
          <th class="py-3 pr-4">Estado</th>
          <th class="py-3">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($doctors as $doc): ?>
          <?php $active = (bool) $doc['is_active']; ?>
          <tr class="border-b border-surface-container-high">
            <td class="py-3 pr-3 text-outline"><?= (int) $doc['sort_order'] ?></td>
            <td class="py-3 pr-4">
              <span class="font-bold text-on-surface block"><?= e((string) $doc['name']) ?></span>
              <span class="text-outline text-label-sm"><?= e((string) $doc['slug']) ?></span>
            </td>
            <td class="py-3 pr-4">
              <span class="text-on-surface block"><?= e((string) $doc['specialty_label']) ?></span>
              <span class="text-outline text-label-sm">Categoría: <?= e((string) $doc['category']) ?></span>
            </td>
            <td class="py-3 pr-4">
              <?php if (!empty($doc['primary_specialty_name'])): ?>
                <span class="font-semibold text-primary block"><?= e((string) $doc['primary_specialty_name']) ?></span>
                <?php if (!empty($doc['secondary_specialty_names'])): ?>
                  <span class="text-outline text-label-sm block">+ <?= e(implode(', ', $doc['secondary_specialty_names'])) ?></span>
                <?php endif; ?>
              <?php else: ?>
                <span class="text-outline text-label-sm">Solo acceso directo (?doctor=)</span>
              <?php endif; ?>
            </td>
            <td class="py-3 pr-4 whitespace-nowrap">
              <span class="inline-flex px-3 py-1 rounded-full font-label-sm text-label-sm font-bold uppercase <?= $active ? 'bg-secondary-container text-on-secondary-container' : 'bg-surface-container-high text-outline' ?>">
                <?= $active ? 'Activo' : 'Oculto' ?>
              </span>
            </td>
            <td class="py-3 whitespace-nowrap">
              <div class="flex items-center gap-2">
                <a
                  href="<?= e(url('/admin/medicos') . '?edit=' . (int) $doc['id']) ?>"
                  class="px-3 py-1.5 rounded-full bg-surface-container-low hover:bg-primary hover:text-on-primary text-primary font-label-sm text-label-sm font-bold border border-outline-variant"
                >
                  Editar
                </a>
                <form method="post" action="<?= e(url('/admin/medicos')) ?>">
                  <input type="hidden" name="_csrf" value="<?= e(Security::csrfToken()) ?>" />
                  <input type="hidden" name="_action" value="toggle" />
                  <input type="hidden" name="id" value="<?= (int) $doc['id'] ?>" />
                  <button
                    type="submit"
                    class="px-3 py-1.5 rounded-full bg-surface-container-low hover:bg-surface-container text-on-surface-variant font-label-sm text-label-sm font-semibold border border-outline-variant cursor-pointer"
                  >
                    <?= $active ? 'Ocultar' : 'Activar' ?>
                  </button>
                </form>
                <form method="post" action="<?= e(url('/admin/medicos')) ?>" onsubmit="return confirm('¿Eliminar definitivamente este médico del directorio?');">
                  <input type="hidden" name="_csrf" value="<?= e(Security::csrfToken()) ?>" />
                  <input type="hidden" name="_action" value="delete" />
                  <input type="hidden" name="id" value="<?= (int) $doc['id'] ?>" />
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
