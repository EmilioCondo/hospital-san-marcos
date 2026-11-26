<?php
declare(strict_types=1);

if (!defined('HSM_APP')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

$st = (string) $appointment['status'];
$badgeCls = match ($st) {
    'confirmada' => 'bg-secondary-container text-on-secondary-container',
    'cancelada'  => 'bg-error-container text-on-error-container',
    'atendida'   => 'bg-surface-container-high text-on-surface',
    default      => 'bg-surface-container-highest text-primary',
};

$shiftText = ((string) $appointment['shift']) === 'vespertino'
    ? 'Tarde (14:00 - 18:00)'
    : 'Mañana (08:00 - 12:00)';

$phoneDigits = preg_replace('/\D+/', '', (string) $appointment['patient_phone']) ?? '';
$waPhone = str_starts_with($phoneDigits, '0') ? ('593' . substr($phoneDigits, 1)) : $phoneDigits;
$hasPatientEmail = !empty($appointment['patient_email']);
?>
<div class="space-y-6 animate-fadeIn">
  <div class="flex flex-wrap items-center justify-between gap-3">
    <div class="space-y-1">
      <a href="<?= e(url('/admin/citas')) ?>" class="inline-flex items-center gap-1 font-label-md text-label-md text-primary hover:underline">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span>
        <span>Volver al listado de citas</span>
      </a>
      <h1 class="font-headline-md text-headline-md text-on-surface font-semibold">
        Solicitud <?= e((string) $appointment['code']) ?>
      </h1>
    </div>
    <span class="inline-flex px-4 py-1.5 rounded-full font-label-md text-label-md font-bold uppercase <?= $badgeCls ?>">
      Estado actual: <?= e($st) ?>
    </span>
  </div>

  <?php if ($formErrors !== []): ?>
    <div role="alert" class="p-4 rounded-xl bg-error-container text-on-error-container border border-outline-variant space-y-1">
      <p class="font-title-md text-body-sm font-bold">Corrige lo siguiente antes de guardar:</p>
      <ul class="font-body-sm text-body-sm pl-5">
        <?php foreach ($formErrors as $err): ?>
          <li>• <?= e($err) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    <!-- Ficha Informativa de la Solicitud (7/8 cols) -->
    <div class="lg:col-span-8 bg-surface-container-lowest p-6 rounded-2xl shadow-sm border border-outline-variant space-y-6">
      <h2 class="font-headline-sm text-title-lg font-bold text-on-surface border-b border-surface-container-high pb-3">
        Información registrada por el paciente
      </h2>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="p-4 rounded-xl bg-surface-container-low border border-outline-variant space-y-1">
          <span class="font-label-sm text-label-sm uppercase text-outline font-bold block">Especialidad / Servicio</span>
          <p class="font-title-md text-body-md font-bold text-on-surface"><?= e((string) $appointment['attention_label']) ?></p>
          <p class="font-body-sm text-body-sm text-on-surface-variant"><?= e((string) $appointment['doctor_label']) ?></p>
        </div>

        <div class="p-4 rounded-xl bg-surface-container-low border border-outline-variant space-y-1">
          <span class="font-label-sm text-label-sm uppercase text-outline font-bold block">Fecha y Franja Solicitada</span>
          <p class="font-title-md text-body-md font-bold text-on-surface"><?= e((string) $appointment['appointment_date']) ?></p>
          <p class="font-body-sm text-body-sm text-on-surface-variant"><?= e($shiftText) ?></p>
          <?php if (!empty($appointment['confirmed_time'])): ?>
            <p class="font-label-md text-label-sm font-bold text-secondary">
              Hora confirmada: <?= e((string) $appointment['confirmed_time']) ?>
            </p>
          <?php endif; ?>
        </div>

        <div class="p-4 rounded-xl bg-surface-container-low border border-outline-variant space-y-1">
          <span class="font-label-sm text-label-sm uppercase text-outline font-bold block">Datos del Paciente</span>
          <p class="font-title-md text-body-md font-bold text-on-surface"><?= e((string) $appointment['patient_name']) ?></p>
          <p class="font-body-sm text-body-sm text-on-surface-variant uppercase">
            <?= e((string) $appointment['patient_doc_type']) ?>: <?= e((string) $appointment['patient_id_doc']) ?>
          </p>
        </div>

        <div class="p-4 rounded-xl bg-surface-container-low border border-outline-variant space-y-2">
          <span class="font-label-sm text-label-sm uppercase text-outline font-bold block">Canales de Contacto</span>
          <p class="font-body-sm text-body-sm text-on-surface font-semibold">
            Tel: <a href="tel:<?= e($phoneDigits) ?>" class="link-primary"><?= e((string) $appointment['patient_phone']) ?></a>
          </p>
          <p class="font-body-sm text-body-sm text-on-surface-variant">
            <?= $hasPatientEmail ? e((string) $appointment['patient_email']) : 'Sin correo registrado' ?>
          </p>
          <?php if ($waPhone !== ''): ?>
            <a
              href="https://wa.me/<?= e($waPhone) ?>"
              target="_blank"
              rel="noopener noreferrer"
              class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-secondary text-on-secondary font-label-sm text-label-sm font-semibold"
            >
              <span class="material-symbols-outlined text-[15px]">chat</span>
              <span>Abrir WhatsApp del paciente</span>
            </a>
          <?php endif; ?>
        </div>
      </div>

      <div class="p-4 rounded-xl bg-surface-container-low border border-outline-variant space-y-1">
        <span class="font-label-sm text-label-sm uppercase text-outline font-bold block">Motivo o Comentarios del Paciente</span>
        <p class="font-body-sm text-body-sm text-on-surface leading-relaxed">
          <?= !empty($appointment['reason']) ? nl2br(e((string) $appointment['reason']), false) : 'Sin comentarios adicionales.' ?>
        </p>
      </div>

      <div class="flex flex-wrap items-center justify-between gap-2 font-label-sm text-label-sm text-outline pt-2 border-t border-surface-container-high">
        <span>Registrada el: <?= e((string) $appointment['created_at']) ?></span>
        <span>Estado último correo: <strong><?= e((string) $appointment['email_status']) ?></strong></span>
      </div>
    </div>

    <!-- Formulario de Gestión de Admisión (4 cols) -->
    <div class="lg:col-span-4 bg-surface-container-lowest p-6 rounded-2xl shadow-sm border border-outline-variant space-y-5">
      <h2 class="font-headline-sm text-title-lg font-bold text-on-surface border-b border-surface-container-high pb-3">
        Resolución de Admisión
      </h2>

      <form method="post" action="<?= e(url('/admin/citas/ver') . '?id=' . (int) $appointment['id']) ?>" class="space-y-4">
        <input type="hidden" name="_csrf" value="<?= e(Security::csrfToken()) ?>" />
        <input type="hidden" name="id" value="<?= (int) $appointment['id'] ?>" />

        <div class="space-y-2">
          <label for="appt-status" class="block font-title-md text-label-md font-semibold text-on-surface">
            Estado de la cita
          </label>
          <select
            id="appt-status"
            name="status"
            class="w-full bg-surface-container-low text-on-surface px-4 py-3 rounded-xl font-body-md text-body-md border border-outline-variant focus-primary"
          >
            <?php foreach (['pendiente' => 'Pendiente de confirmar', 'confirmada' => 'Confirmada', 'atendida' => 'Atendida', 'cancelada' => 'Cancelada'] as $val => $lbl): ?>
              <option value="<?= e($val) ?>" <?= $st === $val ? 'selected' : '' ?>>
                <?= e($lbl) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="space-y-2">
          <label for="appt-time" class="block font-title-md text-label-md font-semibold text-on-surface">
            Hora asignada <span class="text-outline text-label-sm font-normal">(Requerida al confirmar)</span>
          </label>
          <input
            id="appt-time"
            name="confirmed_time"
            type="time"
            value="<?= e((string) ($appointment['confirmed_time'] ?? '')) ?>"
            class="w-full bg-surface-container-low text-on-surface px-4 py-3 rounded-xl font-body-md text-body-md border border-outline-variant focus-primary"
          />
        </div>

        <div class="space-y-2">
          <label for="appt-notes" class="block font-title-md text-label-md font-semibold text-on-surface">
            Indicaciones u observaciones
          </label>
          <textarea
            id="appt-notes"
            name="admin_notes"
            rows="3"
            maxlength="600"
            placeholder="Ej. Acudir en ayunas, traer exámenes previos o motivo de reprogramación..."
            class="w-full bg-surface-container-low text-on-surface placeholder:text-outline p-3.5 rounded-xl font-body-sm text-body-sm border border-outline-variant focus-primary resize-none"
          ><?= e((string) ($appointment['admin_notes'] ?? '')) ?></textarea>
        </div>

        <?php if ($hasPatientEmail): ?>
          <div class="p-3.5 rounded-xl bg-surface-container-low border border-outline-variant">
            <label class="flex items-start gap-2.5 cursor-pointer">
              <input
                type="checkbox"
                name="notify_patient"
                value="1"
                checked
                class="mt-1 w-4 h-4 accent-primary rounded"
              />
              <span class="font-body-sm text-label-sm text-on-surface leading-snug">
                Enviar notificación automática al correo del paciente (<strong><?= e((string) $appointment['patient_email']) ?></strong>) al marcar como <strong>confirmada</strong> o <strong>cancelada</strong>.
              </span>
            </label>
          </div>
        <?php else: ?>
          <p class="font-label-sm text-label-sm text-outline">
            El paciente no registró correo electrónico; confirma el turno por teléfono o WhatsApp.
          </p>
        <?php endif; ?>

        <button
          type="submit"
          class="w-full inline-flex items-center justify-center gap-2 bg-primary text-on-primary hover:bg-on-primary-container transition-colors font-title-md text-body-md font-bold px-6 py-3.5 rounded-full cursor-pointer shadow-sm focus-primary"
        >
          <span class="material-symbols-outlined text-[20px]">save</span>
          <span>Guardar cambios</span>
        </button>
      </form>
    </div>
  </div>
</div>
