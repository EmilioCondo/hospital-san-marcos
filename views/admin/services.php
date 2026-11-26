<?php
declare(strict_types=1);

if (!defined('HSM_APP')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

$isEditSvc = is_array($editingSvc) && !empty($editingSvc['id']);
$featText  = is_array($editingSvc['features'] ?? null) ? implode("\n", $editingSvc['features']) : '';
?>
<div class="space-y-8 animate-fadeIn">
  <div class="flex flex-wrap items-center justify-between gap-3">
    <div>
      <span class="font-label-sm text-label-sm text-primary uppercase tracking-wider font-bold block">
        Catálogo Institucional
      </span>
      <h1 class="font-headline-md text-headline-md text-on-surface font-semibold">
        Servicios Clínicos y Fichas Operativas
      </h1>
    </div>
    <?php if (!$showSvcForm && $editingDetail === null): ?>
      <a
        href="<?= e(url('/admin/servicios') . '?new=1') ?>"
        class="inline-flex items-center gap-2 bg-primary text-on-primary font-title-md text-label-md font-bold px-5 py-2.5 rounded-full hover:bg-on-primary-container transition-colors shadow-sm focus-primary"
      >
        <span class="material-symbols-outlined text-[18px]">add_circle</span>
        <span>Nuevo servicio</span>
      </a>
    <?php endif; ?>
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

  <?php if ($showSvcForm): ?>
    <!-- Formulario de Servicio de Cuadrícula -->
    <div class="bg-surface-container-lowest p-6 rounded-2xl shadow-sm border border-outline-variant space-y-5">
      <div class="flex items-center justify-between border-b border-surface-container-high pb-3">
        <h2 class="font-headline-sm text-title-lg font-bold text-on-surface">
          <?= $isEditSvc ? 'Editar servicio: ' . e((string) $editingSvc['name']) : 'Nuevo servicio en catálogo' ?>
        </h2>
        <a href="<?= e(url('/admin/servicios')) ?>" class="font-label-md text-label-md text-outline hover:text-on-surface">
          Cancelar
        </a>
      </div>

      <form method="post" action="<?= e(url('/admin/servicios')) ?>" class="space-y-4">
        <input type="hidden" name="_csrf" value="<?= e(Security::csrfToken()) ?>" />
        <input type="hidden" name="_action" value="save_service" />
        <input type="hidden" name="id" value="<?= $isEditSvc ? (int) $editingSvc['id'] : 0 ?>" />

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="space-y-1.5">
            <label for="svc-name" class="block font-title-md text-label-md font-semibold text-on-surface">
              Nombre del servicio <span class="text-tertiary">*</span>
            </label>
            <input
              id="svc-name"
              name="name"
              type="text"
              required
              value="<?= e((string) ($editingSvc['name'] ?? '')) ?>"
              class="w-full bg-surface-container-low text-on-surface px-4 py-2.5 rounded-xl font-body-sm text-body-sm border border-outline-variant focus-primary"
            />
          </div>

          <div class="space-y-1.5">
            <label for="svc-tag" class="block font-title-md text-label-md font-semibold text-on-surface">
              Etiqueta superior <span class="text-tertiary">*</span>
            </label>
            <input
              id="svc-tag"
              name="tag"
              type="text"
              required
              placeholder="Ej. Agenda programada"
              value="<?= e((string) ($editingSvc['tag'] ?? '')) ?>"
              class="w-full bg-surface-container-low text-on-surface px-4 py-2.5 rounded-xl font-body-sm text-body-sm border border-outline-variant focus-primary"
            />
          </div>

          <div class="space-y-1.5">
            <label for="svc-tag-class" class="block font-title-md text-label-md font-semibold text-on-surface">
              Color de etiqueta
            </label>
            <select
              id="svc-tag-class"
              name="tag_class"
              class="w-full bg-surface-container-low text-on-surface px-4 py-2.5 rounded-xl font-body-sm text-body-sm border border-outline-variant focus-primary"
            >
              <?php foreach ($allowedTagClasses as $clsVal => $clsLbl): ?>
                <option value="<?= e($clsVal) ?>" <?= (($editingSvc['tag_class'] ?? '') === $clsVal) ? 'selected' : '' ?>>
                  <?= e($clsLbl) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="space-y-1.5">
            <label for="svc-icon" class="block font-title-md text-label-md font-semibold text-on-surface">
              Ícono Material Symbols <span class="text-tertiary">*</span>
            </label>
            <input
              id="svc-icon"
              name="icon"
              type="text"
              required
              value="<?= e((string) ($editingSvc['icon'] ?? 'medical_services')) ?>"
              class="w-full bg-surface-container-low text-on-surface px-4 py-2.5 rounded-xl font-body-sm text-body-sm border border-outline-variant focus-primary"
            />
          </div>

          <div class="space-y-1.5">
            <label for="svc-detail-key" class="block font-title-md text-label-md font-semibold text-on-surface">
              Ficha operativa asociada
            </label>
            <select
              id="svc-detail-key"
              name="detail_key"
              class="w-full bg-surface-container-low text-on-surface px-4 py-2.5 rounded-xl font-body-sm text-body-sm border border-outline-variant focus-primary"
            >
              <?php foreach ($allowedDetailKeys as $dk): ?>
                <option value="<?= e($dk) ?>" <?= (($editingSvc['detail_key'] ?? 'laboratorio') === $dk) ? 'selected' : '' ?>>
                  <?= e($dk) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="grid grid-cols-2 gap-2">
            <div class="space-y-1.5">
              <label for="svc-order" class="block font-title-md text-label-md font-semibold text-on-surface">
                Orden
              </label>
              <input
                id="svc-order"
                name="sort_order"
                type="number"
                min="1"
                value="<?= (int) ($editingSvc['sort_order'] ?? (count($services) + 1)) ?>"
                class="w-full bg-surface-container-low text-on-surface px-3 py-2.5 rounded-xl font-body-sm text-body-sm border border-outline-variant focus-primary"
              />
            </div>
            <div class="space-y-1.5">
              <label for="svc-slug" class="block font-title-md text-label-md font-semibold text-on-surface">
                Slug
              </label>
              <input
                id="svc-slug"
                name="slug"
                type="text"
                placeholder="Auto"
                value="<?= e((string) ($editingSvc['slug'] ?? '')) ?>"
                class="w-full bg-surface-container-low text-on-surface px-3 py-2.5 rounded-xl font-body-sm text-body-sm border border-outline-variant focus-primary"
              />
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="space-y-1.5">
            <label for="svc-desc" class="block font-title-md text-label-md font-semibold text-on-surface">
              Descripción del servicio <span class="text-tertiary">*</span>
            </label>
            <textarea
              id="svc-desc"
              name="description"
              rows="3"
              required
              class="w-full bg-surface-container-low text-on-surface p-3.5 rounded-xl font-body-sm text-body-sm border border-outline-variant focus-primary resize-none"
            ><?= e((string) ($editingSvc['description'] ?? '')) ?></textarea>
          </div>

          <div class="space-y-1.5">
            <label for="svc-features" class="block font-title-md text-label-md font-semibold text-on-surface">
              Características destacadas (una por línea) <span class="text-tertiary">*</span>
            </label>
            <textarea
              id="svc-features"
              name="features_lines"
              rows="3"
              required
              placeholder="Turnos matutinos y vespertinos&#10;Historia clínica digital centralizada"
              class="w-full bg-surface-container-low text-on-surface p-3.5 rounded-xl font-body-sm text-body-sm border border-outline-variant focus-primary resize-none"
            ><?= e($featText) ?></textarea>
          </div>
        </div>

        <div class="pt-2 flex items-center gap-3">
          <button
            type="submit"
            class="inline-flex items-center gap-2 bg-primary text-on-primary hover:bg-on-primary-container font-title-md text-label-md font-bold px-6 py-3 rounded-full cursor-pointer shadow-sm focus-primary"
          >
            <span class="material-symbols-outlined text-[18px]">save</span>
            <span><?= $isEditSvc ? 'Guardar servicio' : 'Crear servicio' ?></span>
          </button>
          <a href="<?= e(url('/admin/servicios')) ?>" class="px-5 py-3 rounded-full bg-surface-container-low text-on-surface-variant border border-outline-variant font-label-md text-label-md">
            Cancelar
          </a>
        </div>
      </form>
    </div>
  <?php endif; ?>

  <?php if ($editingDetail !== null): ?>
    <!-- Formulario de Ficha Operativa Interactiva -->
    <div class="bg-surface-container-lowest p-6 rounded-2xl shadow-sm border border-outline-variant space-y-5">
      <div class="flex items-center justify-between border-b border-surface-container-high pb-3">
        <h2 class="font-headline-sm text-title-lg font-bold text-on-surface">
          Editar Ficha Operativa (pestaña «<?= e((string) $editingDetail['detail_key']) ?>»)
        </h2>
        <a href="<?= e(url('/admin/servicios')) ?>" class="font-label-md text-label-md text-outline hover:text-on-surface">
          Cancelar
        </a>
      </div>

      <form method="post" action="<?= e(url('/admin/servicios')) ?>" class="space-y-4">
        <input type="hidden" name="_csrf" value="<?= e(Security::csrfToken()) ?>" />
        <input type="hidden" name="_action" value="save_detail" />
        <input type="hidden" name="id" value="<?= (int) $editingDetail['id'] ?>" />

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="space-y-1.5">
            <label for="det-title" class="block font-title-md text-label-md font-semibold text-on-surface">
              Título de la ficha <span class="text-tertiary">*</span>
            </label>
            <input
              id="det-title"
              name="title"
              type="text"
              required
              value="<?= e((string) $editingDetail['title']) ?>"
              class="w-full bg-surface-container-low text-on-surface px-4 py-2.5 rounded-xl font-body-sm text-body-sm border border-outline-variant focus-primary"
            />
          </div>

          <div class="space-y-1.5">
            <label for="det-tag" class="block font-title-md text-label-md font-semibold text-on-surface">
              Subtítulo / Etiqueta <span class="text-tertiary">*</span>
            </label>
            <input
              id="det-tag"
              name="tag"
              type="text"
              required
              value="<?= e((string) $editingDetail['tag']) ?>"
              class="w-full bg-surface-container-low text-on-surface px-4 py-2.5 rounded-xl font-body-sm text-body-sm border border-outline-variant focus-primary"
            />
          </div>

          <div class="space-y-1.5">
            <label for="det-icon" class="block font-title-md text-label-md font-semibold text-on-surface">
              Ícono Material Symbols <span class="text-tertiary">*</span>
            </label>
            <input
              id="det-icon"
              name="icon"
              type="text"
              required
              value="<?= e((string) $editingDetail['icon']) ?>"
              class="w-full bg-surface-container-low text-on-surface px-4 py-2.5 rounded-xl font-body-sm text-body-sm border border-outline-variant focus-primary"
            />
          </div>
        </div>

        <div class="space-y-1.5">
          <label for="det-desc" class="block font-title-md text-label-md font-semibold text-on-surface">
            Descripción general <span class="text-tertiary">*</span>
          </label>
          <textarea
            id="det-desc"
            name="description"
            rows="2"
            required
            class="w-full bg-surface-container-low text-on-surface p-3.5 rounded-xl font-body-sm text-body-sm border border-outline-variant focus-primary resize-none"
          ><?= e((string) $editingDetail['description']) ?></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="space-y-1.5">
            <label for="det-scope" class="block font-title-md text-label-md font-semibold text-on-surface">
              Ítems de Alcance Clínico (uno por línea) <span class="text-tertiary">*</span>
            </label>
            <textarea
              id="det-scope"
              name="scope_lines"
              rows="4"
              required
              class="w-full bg-surface-container-low text-on-surface p-3.5 rounded-xl font-body-sm text-body-sm border border-outline-variant focus-primary resize-none"
            ><?= e(implode("\n", $editingDetail['scope'] ?? [])) ?></textarea>
          </div>

          <div class="space-y-1.5">
            <label for="det-prep" class="block font-title-md text-label-md font-semibold text-on-surface">
              Requisitos de Preparación (uno por línea) <span class="text-tertiary">*</span>
            </label>
            <textarea
              id="det-prep"
              name="prep_lines"
              rows="4"
              required
              class="w-full bg-surface-container-low text-on-surface p-3.5 rounded-xl font-body-sm text-body-sm border border-outline-variant focus-primary resize-none"
            ><?= e(implode("\n", $editingDetail['prep'] ?? [])) ?></textarea>
          </div>

          <div class="space-y-1.5">
            <label for="det-time" class="block font-title-md text-label-md font-semibold text-on-surface">
              Aviso de tiempos de entrega / atención <span class="text-tertiary">*</span>
            </label>
            <input
              id="det-time"
              name="time_notice"
              type="text"
              required
              value="<?= e((string) $editingDetail['time_notice']) ?>"
              class="w-full bg-surface-container-low text-on-surface px-4 py-2.5 rounded-xl font-body-sm text-body-sm border border-outline-variant focus-primary"
            />
          </div>

          <div class="space-y-1.5">
            <label for="det-prep-notice" class="block font-title-md text-label-md font-semibold text-on-surface">
              Aviso complementario inferior <span class="text-tertiary">*</span>
            </label>
            <input
              id="det-prep-notice"
              name="prep_notice"
              type="text"
              required
              value="<?= e((string) $editingDetail['prep_notice']) ?>"
              class="w-full bg-surface-container-low text-on-surface px-4 py-2.5 rounded-xl font-body-sm text-body-sm border border-outline-variant focus-primary"
            />
          </div>
        </div>

        <div class="pt-2 flex items-center gap-3">
          <button
            type="submit"
            class="inline-flex items-center gap-2 bg-primary text-on-primary hover:bg-on-primary-container font-title-md text-label-md font-bold px-6 py-3 rounded-full cursor-pointer shadow-sm focus-primary"
          >
            <span class="material-symbols-outlined text-[18px]">save</span>
            <span>Actualizar ficha operativa</span>
          </button>
          <a href="<?= e(url('/admin/servicios')) ?>" class="px-5 py-3 rounded-full bg-surface-container-low text-on-surface-variant border border-outline-variant font-label-md text-label-md">
            Cancelar
          </a>
        </div>
      </form>
    </div>
  <?php endif; ?>

  <!-- Tabla 1: Servicios principales (6 tarjetas) -->
  <div class="bg-surface-container-lowest p-5 rounded-2xl shadow-sm border border-outline-variant space-y-4">
    <h2 class="font-headline-sm text-title-lg font-bold text-on-surface">
      1. Tarjetas de Servicios Principales (<?= count($services) ?>)
    </h2>
    <div class="overflow-x-auto">
      <table class="w-full text-left font-body-sm text-body-sm">
        <thead>
          <tr class="border-b border-outline-variant text-outline font-label-sm text-label-sm uppercase">
            <th class="py-2.5 pr-3">#</th>
            <th class="py-2.5 pr-4">Servicio</th>
            <th class="py-2.5 pr-4">Etiqueta</th>
            <th class="py-2.5 pr-4">Características</th>
            <th class="py-2.5">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($services as $svc): ?>
            <tr class="border-b border-surface-container-high">
              <td class="py-3 pr-3 text-outline"><?= (int) $svc['sort_order'] ?></td>
              <td class="py-3 pr-4">
                <div class="flex items-center gap-2">
                  <span class="material-symbols-outlined text-primary text-[20px]"><?= e((string) $svc['icon']) ?></span>
                  <div>
                    <span class="font-bold text-on-surface block"><?= e((string) $svc['name']) ?></span>
                    <span class="text-outline text-label-sm">Ficha: <?= e((string) $svc['detail_key']) ?></span>
                  </div>
                </div>
              </td>
              <td class="py-3 pr-4"><?= e((string) $svc['tag']) ?></td>
              <td class="py-3 pr-4 text-on-surface-variant"><?= count($svc['features']) ?> ítems</td>
              <td class="py-3 whitespace-nowrap">
                <div class="flex items-center gap-2">
                  <a
                    href="<?= e(url('/admin/servicios') . '?edit=' . (int) $svc['id']) ?>"
                    class="px-3 py-1.5 rounded-full bg-surface-container-low hover:bg-primary hover:text-on-primary text-primary font-label-sm text-label-sm font-bold border border-outline-variant"
                  >
                    Editar
                  </a>
                  <form method="post" action="<?= e(url('/admin/servicios')) ?>" onsubmit="return confirm('¿Eliminar este servicio del catálogo?');">
                    <input type="hidden" name="_csrf" value="<?= e(Security::csrfToken()) ?>" />
                    <input type="hidden" name="_action" value="delete_service" />
                    <input type="hidden" name="id" value="<?= (int) $svc['id'] ?>" />
                    <button type="submit" class="px-3 py-1.5 rounded-full bg-error-container text-on-error-container font-label-sm text-label-sm font-bold cursor-pointer">
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

  <!-- Tabla 2: 4 Fichas Operativas Interactivas -->
  <div class="bg-surface-container-lowest p-5 rounded-2xl shadow-sm border border-outline-variant space-y-4">
    <h2 class="font-headline-sm text-title-lg font-bold text-on-surface">
      2. Fichas Operativas por Pestaña (Preparación y Alcance)
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <?php foreach ($details as $det): ?>
        <div class="p-4 rounded-xl bg-surface-container-low border border-outline-variant flex items-start justify-between gap-3">
          <div class="space-y-1">
            <span class="font-label-sm text-label-sm uppercase text-primary font-bold">Pestaña: <?= e((string) $det['detail_key']) ?></span>
            <p class="font-title-md text-body-md font-bold text-on-surface"><?= e((string) $det['title']) ?></p>
            <p class="font-body-sm text-label-sm text-on-surface-variant">
              Alcance: <?= count($det['scope']) ?> ítems • Preparación: <?= count($det['prep']) ?> ítems
            </p>
          </div>
          <a
            href="<?= e(url('/admin/servicios') . '?edit_detail=' . (int) $det['id']) ?>"
            class="px-3.5 py-1.5 rounded-full bg-surface-container-lowest hover:bg-primary hover:text-on-primary text-primary font-label-sm text-label-sm font-bold border border-outline-variant shrink-0"
          >
            Editar ficha
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
