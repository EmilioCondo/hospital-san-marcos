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
        Seguridad y Accesos
      </span>
      <h1 class="font-headline-md text-headline-md text-on-surface font-semibold">
        Usuarios Administrativos (<?= count($users) ?>)
      </h1>
    </div>
    <?php if (!$showForm): ?>
      <a
        href="<?= e(url('/admin/usuarios') . '?new=1') ?>"
        class="inline-flex items-center gap-2 bg-primary text-on-primary font-title-md text-label-md font-bold px-5 py-2.5 rounded-full hover:bg-on-primary-container transition-colors shadow-sm focus-primary"
      >
        <span class="material-symbols-outlined text-[18px]">person_add</span>
        <span>Nuevo usuario</span>
      </a>
    <?php endif; ?>
  </div>

  <?php if ($showForm): ?>
    <div class="bg-surface-container-lowest p-6 rounded-2xl shadow-sm border border-outline-variant space-y-5">
      <div class="flex items-center justify-between border-b border-surface-container-high pb-3">
        <h2 class="font-headline-sm text-title-lg font-bold text-on-surface">
          <?= $isEdit ? 'Editar cuenta: ' . e((string) $editing['username']) : 'Crear cuenta administrativa' ?>
        </h2>
        <a href="<?= e(url('/admin/usuarios')) ?>" class="font-label-md text-label-md text-outline hover:text-on-surface">
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

      <form method="post" action="<?= e(url('/admin/usuarios')) ?>" class="space-y-4">
        <input type="hidden" name="_csrf" value="<?= e(Security::csrfToken()) ?>" />
        <input type="hidden" name="_action" value="save" />
        <input type="hidden" name="id" value="<?= $isEdit ? (int) $editing['id'] : 0 ?>" />

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <div class="space-y-1.5">
            <label for="usr-username" class="block font-title-md text-label-md font-semibold text-on-surface">
              Usuario <span class="text-tertiary">*</span>
            </label>
            <input
              id="usr-username"
              name="username"
              type="text"
              required
              autocomplete="off"
              value="<?= e((string) ($editing['username'] ?? '')) ?>"
              placeholder="Ej. recepcion_pasaje"
              class="w-full bg-surface-container-low text-on-surface px-4 py-2.5 rounded-xl font-body-sm text-body-sm border border-outline-variant focus-primary"
            />
          </div>

          <div class="space-y-1.5">
            <label for="usr-email" class="block font-title-md text-label-md font-semibold text-on-surface">
              Correo electrónico <span class="text-tertiary">*</span>
            </label>
            <input
              id="usr-email"
              name="email"
              type="email"
              required
              autocomplete="off"
              value="<?= e((string) ($editing['email'] ?? '')) ?>"
              class="w-full bg-surface-container-low text-on-surface px-4 py-2.5 rounded-xl font-body-sm text-body-sm border border-outline-variant focus-primary"
            />
          </div>

          <div class="space-y-1.5">
            <label for="usr-role" class="block font-title-md text-label-md font-semibold text-on-surface">
              Rol de permisos <span class="text-tertiary">*</span>
            </label>
            <select
              id="usr-role"
              name="role"
              class="w-full bg-surface-container-low text-on-surface px-4 py-2.5 rounded-xl font-body-sm text-body-sm border border-outline-variant focus-primary"
            >
              <option value="recepcion" <?= (($editing['role'] ?? 'recepcion') === 'recepcion') ? 'selected' : '' ?>>Admisión y Recepción (Citas y Mensajes)</option>
              <option value="editor" <?= (($editing['role'] ?? '') === 'editor') ? 'selected' : '' ?>>Editor Institucional (Catálogos)</option>
              <option value="admin" <?= (($editing['role'] ?? '') === 'admin') ? 'selected' : '' ?>>Administrador General (Acceso Total)</option>
            </select>
          </div>

          <div class="space-y-1.5">
            <label for="usr-pass" class="block font-title-md text-label-md font-semibold text-on-surface">
              <?= $isEdit ? 'Nueva contraseña (opcional)' : 'Contraseña inicial *' ?>
            </label>
            <input
              id="usr-pass"
              name="password"
              type="password"
              autocomplete="new-password"
              <?= $isEdit ? '' : 'required' ?>
              placeholder="<?= $isEdit ? 'Dejar vacío para mantener' : 'Mínimo 8 caracteres' ?>"
              class="w-full bg-surface-container-low text-on-surface px-4 py-2.5 rounded-xl font-body-sm text-body-sm border border-outline-variant focus-primary"
            />
          </div>
        </div>

        <label class="flex items-center gap-2 cursor-pointer font-body-sm text-body-sm text-on-surface">
          <input
            type="checkbox"
            name="is_active"
            value="1"
            <?= (!isset($editing['is_active']) || (bool) $editing['is_active']) ? 'checked' : '' ?>
            class="w-4 h-4 accent-primary rounded"
          />
          <span>Cuenta habilitada para iniciar sesión</span>
        </label>

        <div class="pt-2 flex items-center gap-3">
          <button
            type="submit"
            class="inline-flex items-center gap-2 bg-primary text-on-primary hover:bg-on-primary-container font-title-md text-label-md font-bold px-6 py-3 rounded-full cursor-pointer shadow-sm focus-primary"
          >
            <span class="material-symbols-outlined text-[18px]">save</span>
            <span><?= $isEdit ? 'Guardar cuenta' : 'Crear usuario' ?></span>
          </button>
          <a href="<?= e(url('/admin/usuarios')) ?>" class="px-5 py-3 rounded-full bg-surface-container-low text-on-surface-variant border border-outline-variant font-label-md text-label-md">
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
          <th class="py-3 pr-4">Usuario</th>
          <th class="py-3 pr-4">Correo electrónico</th>
          <th class="py-3 pr-4">Rol</th>
          <th class="py-3 pr-4">Último acceso</th>
          <th class="py-3 pr-4">Estado</th>
          <th class="py-3">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
          <?php
          $active = (bool) $u['is_active'];
          $isSelf = (int) $u['id'] === (int) $adminUser['id'];
          ?>
          <tr class="border-b border-surface-container-high">
            <td class="py-3.5 pr-4 font-bold text-on-surface">
              <?= e((string) $u['username']) ?>
              <?php if ($isSelf): ?>
                <span class="text-primary text-label-sm font-semibold">(Tú)</span>
              <?php endif; ?>
            </td>
            <td class="py-3.5 pr-4 text-on-surface-variant"><?= e((string) $u['email']) ?></td>
            <td class="py-3.5 pr-4 font-semibold text-primary"><?= e(AdminAuth::roleLabel((string) $u['role'])) ?></td>
            <td class="py-3.5 pr-4 text-outline"><?= e((string) ($u['last_login_at'] ?? 'Nunca')) ?></td>
            <td class="py-3.5 pr-4 whitespace-nowrap">
              <span class="inline-flex px-3 py-1 rounded-full font-label-sm text-label-sm font-bold uppercase <?= $active ? 'bg-secondary-container text-on-secondary-container' : 'bg-error-container text-on-error-container' ?>">
                <?= $active ? 'Activa' : 'Inactiva' ?>
              </span>
            </td>
            <td class="py-3.5 whitespace-nowrap">
              <div class="flex items-center gap-2">
                <a
                  href="<?= e(url('/admin/usuarios') . '?edit=' . (int) $u['id']) ?>"
                  class="px-3 py-1.5 rounded-full bg-surface-container-low hover:bg-primary hover:text-on-primary text-primary font-label-sm text-label-sm font-bold border border-outline-variant"
                >
                  Editar
                </a>
                <?php if (!$isSelf): ?>
                  <form method="post" action="<?= e(url('/admin/usuarios')) ?>" onsubmit="return confirm('¿Eliminar esta cuenta administrativa?');">
                    <input type="hidden" name="_csrf" value="<?= e(Security::csrfToken()) ?>" />
                    <input type="hidden" name="_action" value="delete" />
                    <input type="hidden" name="id" value="<?= (int) $u['id'] ?>" />
                    <button type="submit" class="px-3 py-1.5 rounded-full bg-error-container text-on-error-container font-label-sm text-label-sm font-bold cursor-pointer">
                      Eliminar
                    </button>
                  </form>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
