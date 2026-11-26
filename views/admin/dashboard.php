<?php
declare(strict_types=1);

if (!defined('HSM_APP')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}
?>
<div class="space-y-8 animate-fadeIn">
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <span class="font-label-sm text-label-sm text-primary uppercase tracking-wider font-bold block">
        Panel de Control
      </span>
      <h1 class="font-headline-md text-headline-md text-on-surface font-semibold">
        Resumen operativo institucional
      </h1>
    </div>
    <?php if ($canManageOp): ?>
      <div class="flex flex-wrap items-center gap-3">
        <a
          href="<?= e(url('/admin/citas') . '?status=pendiente') ?>"
          class="inline-flex items-center gap-2 bg-primary text-on-primary font-title-md text-label-md font-bold px-5 py-2.5 rounded-full hover:bg-on-primary-container transition-colors shadow-sm focus-primary"
        >
          <span class="material-symbols-outlined text-[18px]">pending_actions</span>
          <span>Ver citas pendientes (<?= (int) $apptCounts['pendiente'] ?>)</span>
        </a>
        <a
          href="<?= e(url('/admin/mensajes') . '?status=nuevo') ?>"
          class="inline-flex items-center gap-2 bg-surface-container-lowest text-primary font-title-md text-label-md font-bold px-5 py-2.5 rounded-full hover:bg-surface-container transition-colors border border-outline-variant focus-primary"
        >
          <span class="material-symbols-outlined text-[18px]">mark_email_unread</span>
          <span>Mensajes nuevos (<?= (int) $msgCounts['nuevo'] ?>)</span>
        </a>
      </div>
    <?php endif; ?>
  </div>

  <!-- KPI Cards -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="bg-surface-container-lowest p-5 rounded-2xl shadow-sm border border-outline-variant space-y-2">
      <div class="flex items-center justify-between">
        <span class="font-label-sm text-label-sm uppercase text-outline font-bold">Citas Pendientes</span>
        <span class="material-symbols-outlined text-tertiary text-[22px]">pending_actions</span>
      </div>
      <p class="font-headline-md text-headline-md font-bold text-on-surface">
        <?= (int) $apptCounts['pendiente'] ?>
      </p>
      <p class="font-label-sm text-label-sm text-on-surface-variant">
        Para hoy: <strong><?= (int) $apptCounts['today_pending'] ?></strong> • Total registradas: <?= (int) $apptCounts['all'] ?>
      </p>
    </div>

    <div class="bg-surface-container-lowest p-5 rounded-2xl shadow-sm border border-outline-variant space-y-2">
      <div class="flex items-center justify-between">
        <span class="font-label-sm text-label-sm uppercase text-outline font-bold">Citas Confirmadas</span>
        <span class="material-symbols-outlined text-secondary text-[22px]">event_available</span>
      </div>
      <p class="font-headline-md text-headline-md font-bold text-on-surface">
        <?= (int) $apptCounts['confirmada'] ?>
      </p>
      <p class="font-label-sm text-label-sm text-on-surface-variant">
        Atendidas: <?= (int) $apptCounts['atendida'] ?> • Canceladas: <?= (int) $apptCounts['cancelada'] ?>
      </p>
    </div>

    <div class="bg-surface-container-lowest p-5 rounded-2xl shadow-sm border border-outline-variant space-y-2">
      <div class="flex items-center justify-between">
        <span class="font-label-sm text-label-sm uppercase text-outline font-bold">Mensajes Nuevos</span>
        <span class="material-symbols-outlined text-primary text-[22px]">mail</span>
      </div>
      <p class="font-headline-md text-headline-md font-bold text-on-surface">
        <?= (int) $msgCounts['nuevo'] ?>
      </p>
      <p class="font-label-sm text-label-sm text-on-surface-variant">
        Leídos: <?= (int) $msgCounts['leido'] ?> • Respondidos: <?= (int) $msgCounts['respondido'] ?>
      </p>
    </div>

    <div class="bg-surface-container-lowest p-5 rounded-2xl shadow-sm border border-outline-variant space-y-2">
      <div class="flex items-center justify-between">
        <span class="font-label-sm text-label-sm uppercase text-outline font-bold">Catálogo Médico</span>
        <span class="material-symbols-outlined text-primary text-[22px]">stethoscope</span>
      </div>
      <p class="font-headline-md text-headline-md font-bold text-on-surface">
        <?= (int) $totalDoctors ?>
      </p>
      <p class="font-label-sm text-label-sm text-on-surface-variant">
        Profesionales activos en <?= (int) $totalSpecs ?> especialidades
      </p>
    </div>
  </div>

  <?php if ($canManageOp): ?>
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <!-- Últimas Solicitudes de Cita -->
      <div class="lg:col-span-8 bg-surface-container-lowest p-6 rounded-2xl shadow-sm border border-outline-variant space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="font-headline-sm text-title-lg font-bold text-on-surface">
            Solicitudes de cita recientes
          </h2>
          <a href="<?= e(url('/admin/citas')) ?>" class="font-label-md text-label-md text-primary hover:underline">
            Ver todas →
          </a>
        </div>

        <?php if ($recentAppts === []): ?>
          <p class="font-body-sm text-body-sm text-outline py-4">
            Aún no se han registrado solicitudes de citas médicas.
          </p>
        <?php else: ?>
          <div class="overflow-x-auto">
            <table class="w-full text-left font-body-sm text-body-sm">
              <thead>
                <tr class="border-b border-outline-variant text-outline font-label-sm text-label-sm uppercase">
                  <th class="py-2 pr-3">Código</th>
                  <th class="py-2 pr-3">Paciente</th>
                  <th class="py-2 pr-3">Especialidad / Fecha</th>
                  <th class="py-2 pr-3">Estado</th>
                  <th class="py-2">Acción</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recentAppts as $row): ?>
                  <tr class="border-b border-surface-container-high">
                    <td class="py-3 pr-3 font-bold text-primary whitespace-nowrap">
                      <?= e((string) $row['code']) ?>
                    </td>
                    <td class="py-3 pr-3">
                      <span class="font-semibold text-on-surface block"><?= e((string) $row['patient_name']) ?></span>
                      <span class="text-outline text-label-sm"><?= e((string) $row['patient_phone']) ?></span>
                    </td>
                    <td class="py-3 pr-3">
                      <span class="text-on-surface block"><?= e((string) $row['attention_label']) ?></span>
                      <span class="text-outline text-label-sm">
                        <?= e((string) $row['appointment_date']) ?>
                        <?= !empty($row['confirmed_time']) ? '• ' . e((string) $row['confirmed_time']) : '(' . e((string) $row['shift']) . ')' ?>
                      </span>
                    </td>
                    <td class="py-3 pr-3 whitespace-nowrap">
                      <?php
                      $st = (string) $row['status'];
                      $badgeCls = match ($st) {
                          'confirmada' => 'bg-secondary-container text-on-secondary-container',
                          'cancelada'  => 'bg-error-container text-on-error-container',
                          'atendida'   => 'bg-surface-container-high text-on-surface',
                          default      => 'bg-surface-container-highest text-primary',
                      };
                      ?>
                      <span class="inline-flex px-3 py-1 rounded-full font-label-sm text-label-sm font-bold uppercase <?= $badgeCls ?>">
                        <?= e($st) ?>
                      </span>
                    </td>
                    <td class="py-3 whitespace-nowrap">
                      <a
                        href="<?= e(url('/admin/citas/ver') . '?id=' . (int) $row['id']) ?>"
                        class="link-primary font-semibold"
                      >
                        Gestionar
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

      <!-- Últimos Mensajes de Contacto -->
      <div class="lg:col-span-4 bg-surface-container-lowest p-6 rounded-2xl shadow-sm border border-outline-variant space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="font-headline-sm text-title-lg font-bold text-on-surface">
            Bandeja de contacto
          </h2>
          <a href="<?= e(url('/admin/mensajes')) ?>" class="font-label-md text-label-md text-primary hover:underline">
            Ver todos →
          </a>
        </div>

        <?php if ($recentMsgs === []): ?>
          <p class="font-body-sm text-body-sm text-outline py-4">
            No hay mensajes recibidos por el formulario de contacto.
          </p>
        <?php else: ?>
          <div class="space-y-3">
            <?php foreach ($recentMsgs as $msg): ?>
              <a
                href="<?= e(url('/admin/mensajes/ver') . '?id=' . (int) $msg['id']) ?>"
                class="block p-3.5 rounded-xl bg-surface-container-low hover:bg-surface-container transition-colors border border-outline-variant space-y-1 focus-primary"
              >
                <div class="flex items-center justify-between gap-2">
                  <span class="font-label-sm text-label-sm font-bold text-primary"><?= e((string) $msg['code']) ?></span>
                  <span class="font-label-sm text-label-sm uppercase px-2.5 py-0.5 rounded-full <?= $msg['status'] === 'nuevo' ? 'bg-primary text-on-primary font-bold' : 'bg-surface-container-high text-on-surface-variant' ?>">
                    <?= e((string) $msg['status']) ?>
                  </span>
                </div>
                <p class="font-title-md text-body-sm font-bold text-on-surface">
                  <?= e((string) $msg['name']) ?>
                </p>
                <p class="font-body-sm text-label-sm text-on-surface-variant">
                  <?= e((string) $msg['subject_label']) ?>
                </p>
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>

  <?php if (AdminAuth::hasRole($adminUser, ['admin', 'editor'])): ?>
    <div class="bg-surface-container-lowest p-6 rounded-2xl shadow-sm border border-outline-variant space-y-4">
      <h2 class="font-headline-sm text-title-lg font-bold text-on-surface">
        Administración de Catálogos y Accesos
      </h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="<?= e(url('/admin/medicos')) ?>" class="p-4 rounded-xl bg-surface-container-low hover:bg-surface-container transition-colors border border-outline-variant space-y-1 focus-primary">
          <span class="material-symbols-outlined text-primary text-[22px]">stethoscope</span>
          <p class="font-title-md text-body-md font-bold text-on-surface">Médicos</p>
          <p class="font-body-sm text-label-sm text-on-surface-variant">Directorio y especialidades de citas</p>
        </a>
        <a href="<?= e(url('/admin/especialidades')) ?>" class="p-4 rounded-xl bg-surface-container-low hover:bg-surface-container transition-colors border border-outline-variant space-y-1 focus-primary">
          <span class="material-symbols-outlined text-primary text-[22px]">medical_services</span>
          <p class="font-title-md text-body-md font-bold text-on-surface">Especialidades</p>
          <p class="font-body-sm text-label-sm text-on-surface-variant">Catálogo oficial de áreas médicas</p>
        </a>
        <a href="<?= e(url('/admin/servicios')) ?>" class="p-4 rounded-xl bg-surface-container-low hover:bg-surface-container transition-colors border border-outline-variant space-y-1 focus-primary">
          <span class="material-symbols-outlined text-primary text-[22px]">local_hospital</span>
          <p class="font-title-md text-body-md font-bold text-on-surface">Servicios</p>
          <p class="font-body-sm text-label-sm text-on-surface-variant">Tarjetas clínicas y características</p>
        </a>
        <a href="<?= e(url('/admin/faqs')) ?>" class="p-4 rounded-xl bg-surface-container-low hover:bg-surface-container transition-colors border border-outline-variant space-y-1 focus-primary">
          <span class="material-symbols-outlined text-primary text-[22px]">help</span>
          <p class="font-title-md text-body-md font-bold text-on-surface">Preguntas Frecuentes</p>
          <p class="font-body-sm text-label-sm text-on-surface-variant">Acordeón informativo de Servicios</p>
        </a>
      </div>
    </div>
  <?php endif; ?>
</div>
