<?php
declare(strict_types=1);

/**
 * Controlador del Panel Administrativo — Bloque 3B:
 * CRUD de Médicos, Especialidades, Servicios/Fichas, FAQs y Usuarios Administrativos.
 */

if (!defined('HSM_APP')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

class AdminCatalogController
{
    public static function handleDoctors(string $method): void
    {
        $adminUser   = self::requireCatalogRole();
        $specialties = SpecialtyRepository::getAllForBooking();
        $formErrors  = [];
        $editing     = null;
        $showForm    = isset($_GET['new']) || isset($_GET['edit']);

        if (isset($_GET['edit'])) {
            $editing = DoctorRepository::adminFindById((int) $_GET['edit']);
            if ($editing === null) {
                self::flashAndRedirect('/admin/medicos', 'error', 'El médico solicitado no existe.');
            }
        }

        if ($method === 'POST') {
            if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
                self::flashAndRedirect('/admin/medicos', 'error', 'El token de seguridad expiró. Intenta nuevamente.');
            }

            $action = sanitize_query_param($_POST['_action'] ?? 'save', 20);
            $postId = (int) ($_POST['id'] ?? 0);

            if ($action === 'toggle' && $postId > 0) {
                DoctorRepository::adminToggleActive($postId);
                self::flashAndRedirect('/admin/medicos', 'success', 'Estado de visibilidad del médico actualizado.');
            }

            if ($action === 'delete' && $postId > 0) {
                DoctorRepository::adminDelete($postId);
                self::flashAndRedirect('/admin/medicos', 'success', 'Registro del médico eliminado correctamente.');
            }

            // Acción: save (crear o actualizar)
            $showForm = true;
            $editId   = $postId > 0 ? $postId : null;

            $name           = self::cleanLine($_POST['name'] ?? '', 150);
            $rawSlug        = self::cleanLine($_POST['slug'] ?? '', 120);
            $slug           = $rawSlug !== '' ? Security::slugify($rawSlug) : Security::slugify($name);
            $specialtyLabel = self::cleanLine($_POST['specialty_label'] ?? '', 150);
            $category       = self::cleanLine($_POST['category'] ?? '', 30);
            $rawInitials    = strtoupper(self::cleanLine($_POST['initials'] ?? '', 6));
            $initials       = $rawInitials !== '' ? $rawInitials : self::buildInitials($name);
            $location       = self::cleanLine($_POST['location'] ?? '', 150);
            $description    = self::cleanText($_POST['description'] ?? '');
            $sortOrder      = max(0, (int) ($_POST['sort_order'] ?? 0));
            $accredited     = isset($_POST['accredited']) && (string) $_POST['accredited'] === '1';
            $isActive       = isset($_POST['is_active']) && (string) $_POST['is_active'] === '1';

            $primarySpecRaw = (int) ($_POST['specialty_id'] ?? 0);
            $primarySpecId  = $primarySpecRaw > 0 ? $primarySpecRaw : null;

            $validSpecIds = array_map(static fn(array $s): int => (int) $s['db_id'], $specialties);
            if ($primarySpecId !== null && !in_array($primarySpecId, $validSpecIds, true)) {
                $formErrors[] = 'La especialidad principal seleccionada no es válida.';
            }

            $secIdsRaw = is_array($_POST['secondary_specialty_ids'] ?? null) ? $_POST['secondary_specialty_ids'] : [];
            $secIds    = [];
            foreach ($secIdsRaw as $sid) {
                $sidInt = (int) $sid;
                if (in_array($sidInt, $validSpecIds, true) && $sidInt !== $primarySpecId) {
                    $secIds[] = $sidInt;
                }
            }

            if (mb_strlen($name, 'UTF-8') < 3) {
                $formErrors[] = 'Ingresa el nombre completo del profesional.';
            }
            if (mb_strlen($specialtyLabel, 'UTF-8') < 3) {
                $formErrors[] = 'Ingresa el título o especialidad visible en la tarjeta.';
            }
            if (!in_array($category, ['cirugia', 'ginecologia', 'medicina-interna', 'traumatologia', 'otras'], true)) {
                $formErrors[] = 'Selecciona una categoría de filtro válida.';
            }
            if ($location === '') {
                $location = 'Sede Pasaje · Consulta programada';
            }
            if (mb_strlen($description, 'UTF-8') < 10) {
                $formErrors[] = 'Ingresa una reseña profesional de al menos 10 caracteres.';
            }
            if (DoctorRepository::slugExists($slug, $editId)) {
                $formErrors[] = "El identificador URL «{$slug}» ya pertenece a otro médico.";
            }

            $data = [
                'id'                      => $editId,
                'slug'                    => $slug,
                'name'                    => $name,
                'specialty_label'         => $specialtyLabel,
                'category'                => $category,
                'initials'                => $initials,
                'location'                => $location,
                'accredited'              => $accredited,
                'description'             => $description,
                'specialty_id'            => $primarySpecId,
                'sort_order'              => $sortOrder > 0 ? $sortOrder : 30,
                'is_active'               => $isActive,
                'secondary_specialty_ids' => $secIds,
            ];

            if ($formErrors === []) {
                DoctorRepository::adminSave($editId, $data, $secIds);
                self::flashAndRedirect(
                    '/admin/medicos',
                    'success',
                    $editId !== null ? "Médico «{$name}» actualizado correctamente." : "Médico «{$name}» agregado al directorio."
                );
            }

            http_response_code(422);
            $editing = $data;
        }

        $doctors    = DoctorRepository::adminGetAll();
        $adminFlash = Security::pullFlash('admin_flash');

        self::render(
            'doctors',
            'Catálogo de Médicos | Panel Hospital San Marcos',
            'medicos',
            $adminUser,
            compact('doctors', 'specialties', 'editing', 'showForm', 'formErrors', 'adminFlash')
        );
    }

    public static function handleSpecialties(string $method): void
    {
        $adminUser  = self::requireCatalogRole();
        $categories = SpecialtyRepository::adminGetCategories();
        $formErrors = [];
        $editing    = null;
        $showForm   = isset($_GET['new']) || isset($_GET['edit']);

        if (isset($_GET['edit'])) {
            $editing = SpecialtyRepository::adminFindById((int) $_GET['edit']);
            if ($editing === null) {
                self::flashAndRedirect('/admin/especialidades', 'error', 'La especialidad indicada no existe.');
            }
        }

        if ($method === 'POST') {
            if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
                self::flashAndRedirect('/admin/especialidades', 'error', 'El token de seguridad expiró.');
            }

            $action = sanitize_query_param($_POST['_action'] ?? 'save', 20);
            $postId = (int) ($_POST['id'] ?? 0);

            if ($action === 'delete' && $postId > 0) {
                SpecialtyRepository::adminDelete($postId);
                self::flashAndRedirect('/admin/especialidades', 'success', 'Especialidad eliminada correctamente.');
            }

            $showForm      = true;
            $editId        = $postId > 0 ? $postId : null;
            $name          = self::cleanLine($_POST['name'] ?? '', 150);
            $rawSlug       = self::cleanLine($_POST['slug'] ?? '', 100);
            $slug          = $rawSlug !== '' ? Security::slugify($rawSlug) : Security::slugify($name);
            $badge         = self::cleanLine($_POST['badge'] ?? '', 60);
            $rawBadgeType  = self::cleanLine($_POST['badge_type'] ?? '', 30);
            $badgeType     = in_array($rawBadgeType, ['highlight', 'pediatria'], true) ? $rawBadgeType : null;
            $icon          = self::cleanLine($_POST['icon'] ?? '', 60);
            $highlightText = self::cleanLine($_POST['highlight_text'] ?? '', 255);
            $description   = self::cleanText($_POST['description'] ?? '');
            $sortOrder     = max(1, (int) ($_POST['sort_order'] ?? 18));

            $validCatIds = array_map(static fn(array $c): int => (int) $c['id'], $categories);
            $rawCats     = is_array($_POST['category_ids'] ?? null) ? $_POST['category_ids'] : [];
            $catIds      = [];
            foreach ($rawCats as $cid) {
                $cidInt = (int) $cid;
                if (in_array($cidInt, $validCatIds, true)) {
                    $catIds[] = $cidInt;
                }
            }

            if (mb_strlen($name, 'UTF-8') < 3) {
                $formErrors[] = 'Ingresa el nombre de la especialidad médica.';
            }
            if ($badge === '') {
                $formErrors[] = 'Ingresa el texto corto de la insignia (badge).';
            }
            if ($icon === '' || preg_match('/^[a-z0-9_]+$/', $icon) !== 1) {
                $formErrors[] = 'Ingresa un nombre válido de ícono Material Symbols (ej. stethoscope).';
            }
            if (mb_strlen($description, 'UTF-8') < 10) {
                $formErrors[] = 'Ingresa una descripción de al menos 10 caracteres.';
            }
            if ($catIds === []) {
                $formErrors[] = 'Selecciona al menos una categoría de filtro para la especialidad.';
            }
            if (SpecialtyRepository::slugExists($slug, $editId)) {
                $formErrors[] = "El slug «{$slug}» ya existe en otra especialidad.";
            }

            $data = [
                'id'             => $editId,
                'slug'           => $slug,
                'name'           => $name,
                'description'    => $description,
                'badge'          => $badge,
                'badge_type'     => $badgeType,
                'icon'           => $icon,
                'highlight_text' => $highlightText !== '' ? $highlightText : null,
                'sort_order'     => $sortOrder,
                'category_ids'   => $catIds,
            ];

            if ($formErrors === []) {
                SpecialtyRepository::adminSave($editId, $data, $catIds);
                self::flashAndRedirect(
                    '/admin/especialidades',
                    'success',
                    $editId !== null ? "Especialidad «{$name}» actualizada." : "Especialidad «{$name}» creada."
                );
            }

            http_response_code(422);
            $editing = $data;
        }

        $specialties = SpecialtyRepository::adminGetAll();
        $adminFlash  = Security::pullFlash('admin_flash');

        self::render(
            'specialties',
            'Catálogo de Especialidades | Panel Hospital San Marcos',
            'especialidades',
            $adminUser,
            compact('specialties', 'categories', 'editing', 'showForm', 'formErrors', 'adminFlash')
        );
    }

    public static function handleServices(string $method): void
    {
        $adminUser     = self::requireCatalogRole();
        $formErrors    = [];
        $editingSvc    = null;
        $editingDetail = null;
        $showSvcForm   = isset($_GET['new']) || isset($_GET['edit']);

        if (isset($_GET['edit'])) {
            $editingSvc = ServiceRepository::adminFindServiceById((int) $_GET['edit']);
            if ($editingSvc === null) {
                self::flashAndRedirect('/admin/servicios', 'error', 'El servicio solicitado no existe.');
            }
        }

        if (isset($_GET['edit_detail'])) {
            $editingDetail = ServiceRepository::adminFindDetailById((int) $_GET['edit_detail']);
            if ($editingDetail === null) {
                self::flashAndRedirect('/admin/servicios', 'error', 'La ficha operativa solicitada no existe.');
            }
        }

        $allowedTagClasses = [
            'bg-surface-container-low text-primary font-semibold' => 'Estándar institucional (Azul clínico)',
            'bg-tertiary text-on-tertiary font-bold uppercase'    => 'Urgencia / Destacado crítico (Rojo)',
        ];
        $allowedDetailKeys = ['laboratorio', 'imagen', 'cirugia', 'hospitalizacion', 'emergencias', 'consulta'];

        if ($method === 'POST') {
            if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
                self::flashAndRedirect('/admin/servicios', 'error', 'El token de seguridad expiró.');
            }

            $action = sanitize_query_param($_POST['_action'] ?? 'save_service', 25);
            $postId = (int) ($_POST['id'] ?? 0);

            if ($action === 'delete_service' && $postId > 0) {
                ServiceRepository::adminDeleteService($postId);
                self::flashAndRedirect('/admin/servicios', 'success', 'Servicio eliminado del catálogo.');
            }

            if ($action === 'save_detail' && $postId > 0) {
                $existingDet = ServiceRepository::adminFindDetailById($postId);
                if ($existingDet === null) {
                    self::flashAndRedirect('/admin/servicios', 'error', 'Ficha operativa no encontrada.');
                }

                $title      = self::cleanLine($_POST['title'] ?? '', 200);
                $tag        = self::cleanLine($_POST['tag'] ?? '', 120);
                $icon       = self::cleanLine($_POST['icon'] ?? '', 60);
                $desc       = self::cleanText($_POST['description'] ?? '');
                $timeNotice = self::cleanLine($_POST['time_notice'] ?? '', 255);
                $prepNotice = self::cleanLine($_POST['prep_notice'] ?? '', 255);
                $scopeItems = self::splitLines($_POST['scope_lines'] ?? '');
                $prepItems  = self::splitLines($_POST['prep_lines'] ?? '');

                if (mb_strlen($title, 'UTF-8') < 4) {
                    $formErrors[] = 'Ingresa el título de la ficha operativa.';
                }
                if ($tag === '' || $timeNotice === '' || $prepNotice === '') {
                    $formErrors[] = 'Completa la etiqueta superior y los avisos de tiempos y preparación.';
                }
                if ($icon === '' || preg_match('/^[a-z0-9_]+$/', $icon) !== 1) {
                    $formErrors[] = 'Ingresa un ícono Material Symbols válido.';
                }
                if (mb_strlen($desc, 'UTF-8') < 10) {
                    $formErrors[] = 'Ingresa la descripción técnica de la ficha.';
                }
                if ($scopeItems === [] || $prepItems === []) {
                    $formErrors[] = 'Ingresa al menos un ítem en Alcance y uno en Preparación (uno por línea).';
                }

                $detData = [
                    'id'          => $postId,
                    'detail_key'  => $existingDet['detail_key'],
                    'title'       => $title,
                    'tag'         => $tag,
                    'description' => $desc,
                    'icon'        => $icon,
                    'time_notice' => $timeNotice,
                    'prep_notice' => $prepNotice,
                    'scope'       => $scopeItems,
                    'prep'        => $prepItems,
                ];

                if ($formErrors === []) {
                    ServiceRepository::adminUpdateDetail($postId, $detData, $scopeItems, $prepItems);
                    self::flashAndRedirect('/admin/servicios', 'success', "Ficha operativa «{$title}» actualizada.");
                }

                http_response_code(422);
                $editingDetail = $detData;
            } else {
                // Guardar servicio de cuadrícula
                $showSvcForm = true;
                $editId      = $postId > 0 ? $postId : null;
                $name        = self::cleanLine($_POST['name'] ?? '', 150);
                $rawSlug     = self::cleanLine($_POST['slug'] ?? '', 100);
                $slug        = $rawSlug !== '' ? Security::slugify($rawSlug) : Security::slugify($name);
                $tag         = self::cleanLine($_POST['tag'] ?? '', 80);
                $tagClass    = trim((string) ($_POST['tag_class'] ?? ''));
                $icon        = self::cleanLine($_POST['icon'] ?? '', 60);
                $detailKey   = self::cleanLine($_POST['detail_key'] ?? '', 40);
                $desc        = self::cleanText($_POST['description'] ?? '');
                $sortOrder   = max(1, (int) ($_POST['sort_order'] ?? 7));
                $features    = self::splitLines($_POST['features_lines'] ?? '');

                if (mb_strlen($name, 'UTF-8') < 3) {
                    $formErrors[] = 'Ingresa el nombre del servicio clínico.';
                }
                if ($tag === '') {
                    $formErrors[] = 'Ingresa el texto de la etiqueta superior.';
                }
                if (!isset($allowedTagClasses[$tagClass])) {
                    $tagClass = 'bg-surface-container-low text-primary font-semibold';
                }
                if ($icon === '' || preg_match('/^[a-z0-9_]+$/', $icon) !== 1) {
                    $formErrors[] = 'Ingresa un ícono Material Symbols válido.';
                }
                if (!in_array($detailKey, $allowedDetailKeys, true)) {
                    $formErrors[] = 'Selecciona una clave de ficha operativa válida.';
                }
                if (mb_strlen($desc, 'UTF-8') < 10) {
                    $formErrors[] = 'Ingresa una descripción del servicio de al menos 10 caracteres.';
                }
                if ($features === []) {
                    $formErrors[] = 'Ingresa al menos una característica destacada (una por línea).';
                }
                if (ServiceRepository::serviceSlugExists($slug, $editId)) {
                    $formErrors[] = "El slug «{$slug}» ya pertenece a otro servicio.";
                }

                $svcData = [
                    'id'          => $editId,
                    'slug'        => $slug,
                    'name'        => $name,
                    'tag'         => $tag,
                    'tag_class'   => $tagClass,
                    'description' => $desc,
                    'icon'        => $icon,
                    'detail_key'  => $detailKey,
                    'sort_order'  => $sortOrder,
                    'features'    => $features,
                ];

                if ($formErrors === []) {
                    ServiceRepository::adminSaveService($editId, $svcData, $features);
                    self::flashAndRedirect(
                        '/admin/servicios',
                        'success',
                        $editId !== null ? "Servicio «{$name}» actualizado." : "Servicio «{$name}» agregado."
                    );
                }

                http_response_code(422);
                $editingSvc = $svcData;
            }
        }

        $services   = ServiceRepository::adminGetServices();
        $details    = ServiceRepository::adminGetDetails();
        $adminFlash = Security::pullFlash('admin_flash');

        self::render(
            'services',
            'Catálogo de Servicios | Panel Hospital San Marcos',
            'servicios',
            $adminUser,
            compact('services', 'details', 'editingSvc', 'editingDetail', 'showSvcForm', 'allowedTagClasses', 'allowedDetailKeys', 'formErrors', 'adminFlash')
        );
    }

    public static function handleFaqs(string $method): void
    {
        $adminUser  = self::requireCatalogRole();
        $formErrors = [];
        $editing    = null;
        $showForm   = isset($_GET['new']) || isset($_GET['edit']);

        if (isset($_GET['edit'])) {
            $editing = FaqRepository::adminFindById((int) $_GET['edit']);
            if ($editing === null) {
                self::flashAndRedirect('/admin/faqs', 'error', 'La pregunta frecuente solicitada no existe.');
            }
        }

        if ($method === 'POST') {
            if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
                self::flashAndRedirect('/admin/faqs', 'error', 'El token de seguridad expiró.');
            }

            $action = sanitize_query_param($_POST['_action'] ?? 'save', 20);
            $postId = (int) ($_POST['id'] ?? 0);

            if ($action === 'toggle' && $postId > 0) {
                FaqRepository::adminToggleActive($postId);
                self::flashAndRedirect('/admin/faqs', 'success', 'Visibilidad de la pregunta frecuente actualizada.');
            }

            if ($action === 'delete' && $postId > 0) {
                FaqRepository::adminDelete($postId);
                self::flashAndRedirect('/admin/faqs', 'success', 'Pregunta frecuente eliminada.');
            }

            $showForm  = true;
            $editId    = $postId > 0 ? $postId : null;
            $question  = self::cleanLine($_POST['question'] ?? '', 255);
            $rawSlug   = self::cleanLine($_POST['slug'] ?? '', 100);
            $slug      = $rawSlug !== '' ? Security::slugify($rawSlug) : Security::slugify($question);
            $answer    = self::cleanText($_POST['answer'] ?? '');
            $sortOrder = max(1, (int) ($_POST['sort_order'] ?? 6));
            $isActive  = isset($_POST['is_active']) && (string) $_POST['is_active'] === '1';

            if (mb_strlen($question, 'UTF-8') < 8) {
                $formErrors[] = 'Ingresa la pregunta completa (al menos 8 caracteres).';
            }
            if (mb_strlen($answer, 'UTF-8') < 15) {
                $formErrors[] = 'Ingresa una respuesta clara de al menos 15 caracteres.';
            }
            if (FaqRepository::slugExists($slug, $editId)) {
                $formErrors[] = "El identificador «{$slug}» ya existe en otra pregunta.";
            }

            $data = [
                'id'         => $editId,
                'slug'       => $slug,
                'question'   => $question,
                'answer'     => $answer,
                'sort_order' => $sortOrder,
                'is_active'  => $isActive,
            ];

            if ($formErrors === []) {
                FaqRepository::adminSave($editId, $data);
                self::flashAndRedirect(
                    '/admin/faqs',
                    'success',
                    $editId !== null ? 'Pregunta frecuente actualizada.' : 'Nueva pregunta frecuente publicada.'
                );
            }

            http_response_code(422);
            $editing = $data;
        }

        $faqs       = FaqRepository::adminGetAll();
        $adminFlash = Security::pullFlash('admin_flash');

        self::render(
            'faqs',
            'Preguntas Frecuentes (FAQs) | Panel Hospital San Marcos',
            'faqs',
            $adminUser,
            compact('faqs', 'editing', 'showForm', 'formErrors', 'adminFlash')
        );
    }

    public static function handleUsers(string $method): void
    {
        $adminUser  = self::requireSuperAdmin();
        $formErrors = [];
        $editing    = null;
        $showForm   = isset($_GET['new']) || isset($_GET['edit']);

        if (isset($_GET['edit'])) {
            $editing = AdminUserRepository::findById((int) $_GET['edit']);
            if ($editing === null) {
                self::flashAndRedirect('/admin/usuarios', 'error', 'El usuario administrativo indicado no existe.');
            }
        }

        if ($method === 'POST') {
            if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
                self::flashAndRedirect('/admin/usuarios', 'error', 'El token de seguridad expiró.');
            }

            $action = sanitize_query_param($_POST['_action'] ?? 'save', 20);
            $postId = (int) ($_POST['id'] ?? 0);

            if ($action === 'delete' && $postId > 0) {
                if ($postId === (int) $adminUser['id']) {
                    self::flashAndRedirect('/admin/usuarios', 'error', 'Por seguridad no puedes eliminar tu propia sesión activa.');
                }
                $target = AdminUserRepository::findById($postId);
                if ($target !== null && $target['role'] === 'admin' && AdminUserRepository::countActiveAdmins($postId) === 0) {
                    self::flashAndRedirect('/admin/usuarios', 'error', 'No se puede eliminar el último administrador activo del sistema.');
                }
                AdminUserRepository::delete($postId);
                self::flashAndRedirect('/admin/usuarios', 'success', 'Cuenta administrativa eliminada.');
            }

            $showForm = true;
            $editId   = $postId > 0 ? $postId : null;
            $username = self::cleanLine($_POST['username'] ?? '', 60);
            $email    = strtolower(self::cleanLine($_POST['email'] ?? '', 150));
            $role     = self::cleanLine($_POST['role'] ?? '', 20);
            $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
            $isActive = isset($_POST['is_active']) && (string) $_POST['is_active'] === '1';

            if (preg_match('/^[a-zA-Z0-9._\-]{3,60}$/', $username) !== 1) {
                $formErrors[] = 'El usuario debe tener entre 3 y 60 caracteres (letras, números, punto, guion o guion bajo).';
            }
            if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                $formErrors[] = 'Ingresa un correo electrónico válido.';
            }
            if (!in_array($role, ['admin', 'editor', 'recepcion'], true)) {
                $formErrors[] = 'Selecciona un rol administrativo válido.';
            }

            if ($editId === null && mb_strlen($password, 'UTF-8') < 8) {
                $formErrors[] = 'La contraseña inicial debe tener al menos 8 caracteres.';
            } elseif ($editId !== null && $password !== '' && mb_strlen($password, 'UTF-8') < 8) {
                $formErrors[] = 'Si ingresas una nueva contraseña, debe tener al menos 8 caracteres.';
            }

            // Impedir auto-bloqueo del administrador en sesión
            if ($editId !== null && $editId === (int) $adminUser['id']) {
                if (!$isActive) {
                    $formErrors[] = 'No puedes desactivar tu propia cuenta en uso.';
                }
                if ($role !== 'admin') {
                    $formErrors[] = 'No puedes quitarte el rol de Administrador General en tu propia sesión.';
                }
            } elseif ($editId !== null && (!$isActive || $role !== 'admin') && AdminUserRepository::countActiveAdmins($editId) === 0) {
                $formErrors[] = 'Debe permanecer al menos un Administrador General activo en el sistema.';
            }

            $conflict = AdminUserRepository::existsConflict($username, $email, $editId);
            if ($conflict !== null) {
                $formErrors[] = $conflict;
            }

            $data = [
                'id'        => $editId,
                'username'  => $username,
                'email'     => $email,
                'role'      => $role,
                'password'  => $password,
                'is_active' => $isActive,
            ];

            if ($formErrors === []) {
                AdminUserRepository::save($editId, $data);
                self::flashAndRedirect(
                    '/admin/usuarios',
                    'success',
                    $editId !== null ? "Cuenta «{$username}» actualizada." : "Cuenta «{$username}» creada correctamente."
                );
            }

            http_response_code(422);
            $editing = $data;
        }

        $users      = AdminUserRepository::getAll();
        $adminFlash = Security::pullFlash('admin_flash');

        self::render(
            'users',
            'Usuarios Administrativos | Panel Hospital San Marcos',
            'usuarios',
            $adminUser,
            compact('users', 'editing', 'showForm', 'formErrors', 'adminFlash')
        );
    }

    /**
     * @return array{id: int, username: string, email: string, role: string}
     */
    private static function requireCatalogRole(): array
    {
        $user = AdminAuth::requireAuth();
        if (!AdminAuth::hasRole($user, ['admin', 'editor'])) {
            http_response_code(403);
            self::flashAndRedirect('/admin', 'error', 'Tu rol actual no tiene permisos para modificar catálogos institucionales.');
        }
        return $user;
    }

    /**
     * @return array{id: int, username: string, email: string, role: string}
     */
    private static function requireSuperAdmin(): array
    {
        $user = AdminAuth::requireAuth();
        if (!AdminAuth::hasRole($user, ['admin'])) {
            http_response_code(403);
            self::flashAndRedirect('/admin', 'error', 'Solo el Administrador General puede gestionar cuentas de usuarios.');
        }
        return $user;
    }

    private static function flashAndRedirect(string $path, string $type, string $message): never
    {
        Security::setFlash('admin_flash', ['type' => $type, 'message' => $message]);
        header('Location: ' . url($path), true, 303);
        exit;
    }

    /**
     * @param array{id: int, username: string, email: string, role: string} $adminUser
     * @param array<string, mixed> $viewData
     */
    private static function render(
        string $viewName,
        string $pageTitle,
        string $activeMenu,
        array $adminUser,
        array $viewData
    ): void {
        extract($viewData, EXTR_SKIP);
        $adminViewFile = dirname(__DIR__, 2) . '/views/admin/' . $viewName . '.php';
        require dirname(__DIR__, 2) . '/views/layouts/admin.php';
    }

    private static function cleanLine(mixed $raw, int $maxLen = 200): string
    {
        if (!is_string($raw)) {
            return '';
        }
        $clean = strip_tags($raw);
        $clean = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $clean) ?? '';
        $clean = trim((string) preg_replace('/\s+/u', ' ', $clean));
        if (mb_strlen($clean, 'UTF-8') > $maxLen) {
            $clean = mb_substr($clean, 0, $maxLen, 'UTF-8');
        }
        return $clean;
    }

    private static function cleanText(mixed $raw): string
    {
        if (!is_string($raw)) {
            return '';
        }
        $clean = strip_tags($raw);
        $clean = str_replace(["\r\n", "\r"], "\n", $clean);
        $clean = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/u', '', $clean) ?? '';
        return trim($clean);
    }

    /**
     * @return array<int, string>
     */
    private static function splitLines(mixed $raw): array
    {
        $text = self::cleanText($raw);
        if ($text === '') {
            return [];
        }
        $out = [];
        foreach (explode("\n", $text) as $line) {
            $item = trim($line, " \t•-*");
            if ($item !== '') {
                $out[] = mb_substr($item, 0, 255, 'UTF-8');
            }
        }
        return $out;
    }

    private static function buildInitials(string $name): string
    {
        $parts = preg_split('/\s+/u', trim($name)) ?: [];
        $letters = '';
        foreach ($parts as $p) {
            if ($p !== '') {
                $letters .= mb_strtoupper(mb_substr($p, 0, 1, 'UTF-8'), 'UTF-8');
            }
            if (mb_strlen($letters, 'UTF-8') >= 2) {
                break;
            }
        }
        return $letters !== '' ? $letters : 'DR';
    }
}
