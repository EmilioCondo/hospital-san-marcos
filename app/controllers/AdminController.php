<?php
declare(strict_types=1);

/**
 * Controlador del Panel Administrativo (/admin) — Bloque 3A:
 * Autenticación, Resumen Operativo, Gestión de Citas y Bandeja de Mensajes.
 */

if (!defined('HSM_APP')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

class AdminController
{
    public static function dispatch(string $route, string $method): void
    {
        AdminAuth::sendNoCacheHeaders();
        Security::ensureSession();

        switch ($route) {
            case '/admin/login':
                self::handleLogin($method);
                return;

            case '/admin/logout':
                self::handleLogout($method);
                return;

            case '/admin':
                self::handleDashboard();
                return;

            case '/admin/citas':
                self::handleAppointmentsList();
                return;

            case '/admin/citas/ver':
                self::handleAppointmentDetail($method);
                return;

            case '/admin/mensajes':
                self::handleMessagesList();
                return;

            case '/admin/mensajes/ver':
                self::handleMessageDetail($method);
                return;

            case '/admin/medicos':
                AdminCatalogController::handleDoctors($method);
                return;

            case '/admin/especialidades':
                AdminCatalogController::handleSpecialties($method);
                return;

            case '/admin/servicios':
                AdminCatalogController::handleServices($method);
                return;

            case '/admin/faqs':
                AdminCatalogController::handleFaqs($method);
                return;

            case '/admin/usuarios':
                AdminCatalogController::handleUsers($method);
                return;

            default:
                header('Location: ' . url('/admin'), true, 302);
                exit;
        }
    }

    private static function handleLogin(string $method): void
    {
        if (AdminAuth::user() !== null) {
            header('Location: ' . url('/admin'), true, 302);
            exit;
        }

        $error        = null;
        $oldUsername  = '';
        $flashNotice  = Security::pullFlash('admin_auth_notice');
        $hasAnyAdmins = AdminUserRepository::countAll() > 0;

        if ($method === 'POST') {
            $oldUsername = sanitize_query_param($_POST['username'] ?? '', 150);
            $password    = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';

            if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
                http_response_code(422);
                $error = 'La sesión del formulario expiró. Intenta iniciar sesión nuevamente.';
            } else {
                $attempt = AdminAuth::attemptLogin($oldUsername, $password);
                if ($attempt['ok']) {
                    header('Location: ' . url('/admin'), true, 303);
                    exit;
                }
                http_response_code(422);
                $error = $attempt['error'];
            }
        }

        $pageTitle = 'Acceso Administrativo | Hospital San Marcos';
        require dirname(__DIR__, 2) . '/views/admin/login.php';
    }

    private static function handleLogout(string $method): void
    {
        if ($method === 'POST' && Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            AdminAuth::logout();
            Security::setFlash('admin_auth_notice', [
                'type'    => 'success',
                'message' => 'Has cerrado sesión correctamente.',
            ]);
            header('Location: ' . url('/admin/login'), true, 303);
            exit;
        }

        header('Location: ' . url('/admin'), true, 302);
        exit;
    }

    private static function handleDashboard(): void
    {
        $adminUser   = AdminAuth::requireAuth();
        $canManageOp = AdminAuth::hasRole($adminUser, ['admin', 'recepcion']);

        $apptCounts    = AppointmentRepository::getStatusCounts();
        $msgCounts     = ContactMessageRepository::getStatusCounts();
        $totalDoctors  = count(DoctorRepository::getAll());
        $totalSpecs    = count(SpecialtyRepository::getAll());
        $recentAppts   = $canManageOp ? AppointmentRepository::getRecent(5) : [];
        $recentMsgs    = $canManageOp ? ContactMessageRepository::getRecent(5) : [];
        $adminFlash    = Security::pullFlash('admin_flash');

        self::renderAdminView(
            'dashboard',
            'Resumen Operativo | Panel Hospital San Marcos',
            'dashboard',
            $adminUser,
            compact('apptCounts', 'msgCounts', 'totalDoctors', 'totalSpecs', 'recentAppts', 'recentMsgs', 'canManageOp', 'adminFlash')
        );
    }

    private static function handleAppointmentsList(): void
    {
        $adminUser = self::requireOperationalRole();

        $filters = [
            'status' => sanitize_query_param($_GET['status'] ?? '', 20),
            'date'   => sanitize_query_param($_GET['fecha'] ?? '', 12),
            'q'      => sanitize_query_param($_GET['q'] ?? '', 80),
        ];
        $page = max(1, (int) ($_GET['page'] ?? 1));

        $counts     = AppointmentRepository::getStatusCounts();
        $pagination = AppointmentRepository::paginate($filters, $page, 15);
        $adminFlash = Security::pullFlash('admin_flash');

        self::renderAdminView(
            'appointments',
            'Gestión de Citas Médicas | Panel Hospital San Marcos',
            'citas',
            $adminUser,
            compact('filters', 'counts', 'pagination', 'adminFlash')
        );
    }

    private static function handleAppointmentDetail(string $method): void
    {
        $adminUser = self::requireOperationalRole();

        $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
        $appointment = $id > 0 ? AppointmentRepository::findById($id) : null;

        if ($appointment === null) {
            Security::setFlash('admin_flash', [
                'type'    => 'error',
                'message' => 'La solicitud de cita indicada no existe.',
            ]);
            header('Location: ' . url('/admin/citas'), true, 302);
            exit;
        }

        $formErrors = [];

        if ($method === 'POST') {
            if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
                http_response_code(422);
                $formErrors[] = 'El token de seguridad expiró. Vuelve a guardar los cambios.';
            } else {
                $newStatus = sanitize_query_param($_POST['status'] ?? '', 20);
                $rawTime   = sanitize_query_param($_POST['confirmed_time'] ?? '', 20);
                $rawNotes  = self::cleanNotes($_POST['admin_notes'] ?? '');
                $notify    = isset($_POST['notify_patient']) && (string) $_POST['notify_patient'] === '1';

                if (!in_array($newStatus, ['pendiente', 'confirmada', 'cancelada', 'atendida'], true)) {
                    $formErrors[] = 'Selecciona un estado válido para la cita.';
                }

                $cleanTime = null;
                if ($rawTime !== '') {
                    if (preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $rawTime) !== 1) {
                        $formErrors[] = 'Ingresa una hora válida en formato 24h (ej. 09:30 o 15:00).';
                    } else {
                        $cleanTime = $rawTime;
                    }
                } elseif ($newStatus === 'confirmada') {
                    $formErrors[] = 'Para marcar la cita como confirmada debes indicar la hora exacta asignada (ej. 09:30).';
                }

                if ($rawNotes !== '' && mb_strlen($rawNotes, 'UTF-8') > 600) {
                    $formErrors[] = 'Las observaciones administrativas no deben superar los 600 caracteres.';
                }
                $cleanNotes = $rawNotes !== '' ? $rawNotes : null;

                if ($formErrors === []) {
                    AppointmentRepository::updateManagement($id, $newStatus, $cleanTime, $cleanNotes);

                    $mailNote = '';
                    $hasPatientEmail = !empty($appointment['patient_email']);
                    if ($notify && $hasPatientEmail && in_array($newStatus, ['confirmada', 'cancelada'], true)) {
                        $sent = Mailer::sendAppointmentStatusUpdate($appointment, $newStatus, $cleanTime, $cleanNotes);
                        AppointmentRepository::updateEmailStatus($id, $sent ? 'enviado' : 'error');
                        $mailNote = $sent
                            ? ' Se envió la notificación automática al correo del paciente.'
                            : ' La cita se actualizó, pero no se pudo despachar el correo al paciente.';
                    }

                    Security::setFlash('admin_flash', [
                        'type'    => 'success',
                        'message' => sprintf('Cita %s actualizada a estado «%s».%s', (string) $appointment['code'], ucfirst($newStatus), $mailNote),
                    ]);

                    header('Location: ' . url('/admin/citas/ver') . '?id=' . $id, true, 303);
                    exit;
                }

                http_response_code(422);
                // Preservar en memoria los valores enviados para re-renderizar el formulario
                $appointment['status']         = $newStatus;
                $appointment['confirmed_time'] = $rawTime;
                $appointment['admin_notes']    = $rawNotes;
            }
        }

        $adminFlash = Security::pullFlash('admin_flash');

        self::renderAdminView(
            'appointment-detail',
            'Cita ' . $appointment['code'] . ' | Panel Hospital San Marcos',
            'citas',
            $adminUser,
            compact('appointment', 'formErrors', 'adminFlash')
        );
    }

    private static function handleMessagesList(): void
    {
        $adminUser = self::requireOperationalRole();

        $filters = [
            'status'  => sanitize_query_param($_GET['status'] ?? '', 20),
            'subject' => sanitize_query_param($_GET['asunto'] ?? '', 40),
            'q'       => sanitize_query_param($_GET['q'] ?? '', 80),
        ];
        $page = max(1, (int) ($_GET['page'] ?? 1));

        $counts     = ContactMessageRepository::getStatusCounts();
        $pagination = ContactMessageRepository::paginate($filters, $page, 15);
        $adminFlash = Security::pullFlash('admin_flash');

        self::renderAdminView(
            'messages',
            'Mensajes de Contacto | Panel Hospital San Marcos',
            'mensajes',
            $adminUser,
            compact('filters', 'counts', 'pagination', 'adminFlash')
        );
    }

    private static function handleMessageDetail(string $method): void
    {
        $adminUser = self::requireOperationalRole();

        $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
        $message = $id > 0 ? ContactMessageRepository::findById($id) : null;

        if ($message === null) {
            Security::setFlash('admin_flash', [
                'type'    => 'error',
                'message' => 'El mensaje solicitado no existe.',
            ]);
            header('Location: ' . url('/admin/mensajes'), true, 302);
            exit;
        }

        if ($method === 'POST') {
            if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
                Security::setFlash('admin_flash', [
                    'type'    => 'error',
                    'message' => 'El token de seguridad expiró. Intenta nuevamente.',
                ]);
            } else {
                $newStatus = sanitize_query_param($_POST['status'] ?? '', 20);
                if (in_array($newStatus, ['nuevo', 'leido', 'respondido'], true)) {
                    ContactMessageRepository::updateStatus($id, $newStatus);
                    Security::setFlash('admin_flash', [
                        'type'    => 'success',
                        'message' => sprintf('Mensaje %s marcado como «%s».', (string) $message['code'], ucfirst($newStatus)),
                    ]);
                }
            }
            header('Location: ' . url('/admin/mensajes/ver') . '?id=' . $id, true, 303);
            exit;
        }

        // Al abrir un mensaje nuevo en lectura GET, marcar automáticamente como leído
        if ((string) $message['status'] === 'nuevo') {
            ContactMessageRepository::updateStatus($id, 'leido');
            $message['status'] = 'leido';
        }

        $adminFlash = Security::pullFlash('admin_flash');

        self::renderAdminView(
            'message-detail',
            'Mensaje ' . $message['code'] . ' | Panel Hospital San Marcos',
            'mensajes',
            $adminUser,
            compact('message', 'adminFlash')
        );
    }

    /**
     * @return array{id: int, username: string, email: string, role: string}
     */
    private static function requireOperationalRole(): array
    {
        $user = AdminAuth::requireAuth();
        if (!AdminAuth::hasRole($user, ['admin', 'recepcion'])) {
            http_response_code(403);
            Security::setFlash('admin_flash', [
                'type'    => 'error',
                'message' => 'Tu rol actual no tiene permisos para acceder al módulo de Admisión y Contacto.',
            ]);
            header('Location: ' . url('/admin'), true, 302);
            exit;
        }
        return $user;
    }

    /**
     * @param array{id: int, username: string, email: string, role: string} $adminUser
     * @param array<string, mixed> $viewData
     */
    private static function renderAdminView(
        string $viewName,
        string $pageTitle,
        string $activeMenu,
        array $adminUser,
        array $viewData = []
    ): void {
        extract($viewData, EXTR_SKIP);
        $adminViewFile = dirname(__DIR__, 2) . '/views/admin/' . $viewName . '.php';
        require dirname(__DIR__, 2) . '/views/layouts/admin.php';
    }

    private static function cleanNotes(mixed $raw): string
    {
        if (!is_string($raw)) {
            return '';
        }
        $clean = strip_tags($raw);
        $clean = str_replace(["\r\n", "\r"], "\n", $clean);
        $clean = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/u', '', $clean) ?? '';
        return trim($clean);
    }
}
