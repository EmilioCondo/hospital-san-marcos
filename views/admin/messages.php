<?php
declare(strict_types=1);

if (!defined('HSM_APP')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

$statusTabs = [
    ''           => ['label' => 'Todos',       'count' => (int) $counts['all']],
    'nuevo'      => ['label' => 'Nuevos',      'count' => (int) $counts['nuevo']],
    'leido'      => ['label' => 'Leídos',      'count' => (int) $counts['leido']],
    'respondido' => ['label' => 'Respondidos', 'count' => (int) $counts['respondido']],
];

$currentStatus  = (string) ($filters['status'] ?? '');
$currentSubject = (string) ($filters['subject'] ?? '');
$currentQuery   = (string) ($filters['q'] ?? '');
?>
<div class="space-y-6 animate-fadeIn">
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
      <span class="font-label-sm text-label-sm text-primary uppercase tracking-wider font-bold block">
        Atención al Ciudadano
      </span>
      <h1 class="font-headline-md text-headline-md text-on-surface font-semibold">
        Bandeja de Mensajes de Contacto
      </h1>
    </div>
    <span class="font-label-md text-label-md text-on-surface-variant">
      Total encontrados: <strong><?= (int) $pagination['total'] ?></strong>
    </span>
  </div>

  <!-- Filtros -->
  <div class="bg-surface-container-lowest p-5 rounded-2xl shadow-sm border border-outline-variant space-y-4">
    <div class="flex flex-wrap items-center gap-2">
      <?php foreach ($statusTabs as $stKey => $tab): ?>
        <?php
        $activeTab = ($currentStatus === $stKey);
        $queryArgs = array_filter([
            'status' => $stKey !== '' ? $stKey : null,
            'asunto' => $currentSubject !== '' ? $currentSubject : null,
            'q'      => $currentQuery !== '' ? $currentQuery : null,
        ]);
        $tabUrl = url('/admin/mensajes') . ($queryArgs !== [] ? '?' . http_build_query($queryArgs) : '');
        ?>
        <a
          href="<?= e($tabUrl) ?>"
          class="px-4 py-2 rounded-full font-label-md text-label-md transition-colors focus-primary <?= $activeTab
              ? 'bg-primary text-on-primary font-bold'
              : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container' ?>"
        >
          <?= e($tab['label']) ?> (<?= $tab['count'] ?>)
        </a>
      <?php endforeach; ?>
    </div>

    <form method="get" action="<?= e(url('/admin/mensajes')) ?>" class="grid grid-cols-1 md:grid-cols-3 gap-3 pt-2">
      <?php if ($currentStatus !== ''): ?>
        <input type="hidden" name="status" value="<?= e($currentStatus) ?>" />
      <?php endif; ?>

      <div>
        <label for="msg-q" class="sr-only">Buscar mensaje</label>
        <input
          id="msg-q"
          type="text"
          name="q"
          value="<?= e($currentQuery) ?>"
          placeholder="Buscar por código CONT-XXXX, nombre o correo..."
          class="w-full bg-surface-container-low text-on-surface placeholder:text-outline px-4 py-2.5 rounded-xl font-body-sm text-body-sm border border-outline-variant focus-primary"
        />
      </div>

      <div>
        <label for="msg-subject" class="sr-only">Filtrar por asunto</label>
        <select
          id="msg-subject"
          name="asunto"
          class="w-full bg-surface-container-low text-on-surface px-4 py-2.5 rounded-xl font-body-sm text-body-sm border border-outline-variant focus-primary"
        >
          <option value="">Todos los asuntos institucionales</option>
          <?php foreach (ContactValidator::SUBJECTS as $slug => $lbl): ?>
            <option value="<?= e($slug) ?>" <?= $currentSubject === $slug ? 'selected' : '' ?>>
              <?= e($lbl) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="flex items-center gap-2">
        <button
          type="submit"
          class="inline-flex items-center justify-center gap-1.5 bg-primary text-on-primary font-title-md text-label-md font-bold px-5 py-2.5 rounded-full hover:bg-on-primary-container transition-colors cursor-pointer focus-primary"
        >
          <span class="material-symbols-outlined text-[18px]">search</span>
          <span>Filtrar</span>
        </button>
        <?php if ($currentStatus !== '' || $currentSubject !== '' || $currentQuery !== ''): ?>
          <a
            href="<?= e(url('/admin/mensajes')) ?>"
            class="inline-flex items-center justify-center px-4 py-2.5 rounded-full bg-surface-container-low text-on-surface-variant hover:bg-surface-container font-label-md text-label-md border border-outline-variant focus-primary"
          >
            Limpiar
          </a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <!-- Tabla de Mensajes -->
  <div class="bg-surface-container-lowest p-5 rounded-2xl shadow-sm border border-outline-variant">
    <?php if ($pagination['items'] === []): ?>
      <div class="text-center py-8 space-y-2">
        <span class="material-symbols-outlined text-[40px] text-outline">inbox</span>
        <p class="font-title-md text-body-md font-semibold text-on-surface">
          No hay mensajes que coincidan con la búsqueda.
        </p>
      </div>
    <?php else: ?>
      <div class="overflow-x-auto">
        <table class="w-full text-left font-body-sm text-body-sm">
          <thead>
            <tr class="border-b border-outline-variant text-outline font-label-sm text-label-sm uppercase">
              <th class="py-3 pr-4">Código</th>
              <th class="py-3 pr-4">Remitente</th>
              <th class="py-3 pr-4">Asunto</th>
              <th class="py-3 pr-4">Recibido</th>
              <th class="py-3 pr-4">Estado</th>
              <th class="py-3">Acción</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($pagination['items'] as $item): ?>
              <?php
              $st = (string) $item['status'];
              $badgeCls = match ($st) {
                  'nuevo'      => 'bg-primary text-on-primary',
                  'respondido' => 'bg-secondary-container text-on-secondary-container',
                  default      => 'bg-surface-container-high text-on-surface',
              };
              ?>
              <tr class="border-b border-surface-container-high">
                <td class="py-3.5 pr-4 font-bold text-primary whitespace-nowrap">
                  <?= e((string) $item['code']) ?>
                </td>
                <td class="py-3.5 pr-4">
                  <span class="<?= $st === 'nuevo' ? 'font-bold' : 'font-semibold' ?> text-on-surface block">
                    <?= e((string) $item['name']) ?>
                  </span>
                  <span class="text-outline text-label-sm block"><?= e((string) $item['email']) ?></span>
                </td>
                <td class="py-3.5 pr-4">
                  <span class="font-semibold text-on-surface block"><?= e((string) $item['subject_label']) ?></span>
                  <span class="text-on-surface-variant text-label-sm block">
                    <?= e(mb_strimwidth((string) $item['message'], 0, 75, '...')) ?>
                  </span>
                </td>
                <td class="py-3.5 pr-4 whitespace-nowrap text-on-surface-variant">
                  <?= e((string) $item['created_at']) ?>
                </td>
                <td class="py-3.5 pr-4 whitespace-nowrap">
                  <span class="inline-flex px-3 py-1 rounded-full font-label-sm text-label-sm font-bold uppercase <?= $badgeCls ?>">
                    <?= e($st) ?>
                  </span>
                </td>
                <td class="py-3.5 whitespace-nowrap">
                  <a
                    href="<?= e(url('/admin/mensajes/ver') . '?id=' . (int) $item['id']) ?>"
                    class="inline-flex items-center gap-1 px-3.5 py-1.5 rounded-full bg-surface-container-low hover:bg-primary hover:text-on-primary text-primary font-label-md text-label-sm font-bold transition-colors border border-outline-variant focus-primary"
                  >
                    <span>Leer mensaje</span>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <?php if ($pagination['totalPages'] > 1): ?>
        <div class="pt-4 mt-4 border-t border-surface-container-high flex items-center justify-between font-label-sm text-label-sm">
          <span class="text-outline">
            Página <?= (int) $pagination['page'] ?> de <?= (int) $pagination['totalPages'] ?>
          </span>
          <div class="flex items-center gap-2">
            <?php if ($pagination['page'] > 1): ?>
              <?php
              $prevArgs = array_filter(['status' => $currentStatus ?: null, 'asunto' => $currentSubject ?: null, 'q' => $currentQuery ?: null, 'page' => $pagination['page'] - 1]);
              ?>
              <a href="<?= e(url('/admin/mensajes') . '?' . http_build_query($prevArgs)) ?>" class="px-3 py-1.5 rounded-full bg-surface-container-low hover:bg-surface-container border border-outline-variant">
                ← Anterior
              </a>
            <?php endif; ?>
            <?php if ($pagination['page'] < $pagination['totalPages']): ?>
              <?php
              $nextArgs = array_filter(['status' => $currentStatus ?: null, 'asunto' => $currentSubject ?: null, 'q' => $currentQuery ?: null, 'page' => $pagination['page'] + 1]);
              ?>
              <a href="<?= e(url('/admin/mensajes') . '?' . http_build_query($nextArgs)) ?>" class="px-3 py-1.5 rounded-full bg-surface-container-low hover:bg-surface-container border border-outline-variant">
                Siguiente →
              </a>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>
