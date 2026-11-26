<?php
declare(strict_types=1);

/**
 * Front Controller y Enrutador Principal
 * Hospital San Marcos - Pasaje, El Oro
 */

define('HSM_APP', true);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/services/Security.php';
require_once __DIR__ . '/app/services/AdminAuth.php';
require_once __DIR__ . '/app/services/Mailer.php';
require_once __DIR__ . '/app/validators/DocumentValidator.php';
require_once __DIR__ . '/app/validators/AppointmentValidator.php';
require_once __DIR__ . '/app/validators/ContactValidator.php';
require_once __DIR__ . '/app/repositories/DoctorRepository.php';
require_once __DIR__ . '/app/repositories/SpecialtyRepository.php';
require_once __DIR__ . '/app/repositories/ServiceRepository.php';
require_once __DIR__ . '/app/repositories/FaqRepository.php';
require_once __DIR__ . '/app/repositories/AppointmentRepository.php';
require_once __DIR__ . '/app/repositories/ContactMessageRepository.php';
require_once __DIR__ . '/app/repositories/AdminUserRepository.php';
require_once __DIR__ . '/app/controllers/AppointmentController.php';
require_once __DIR__ . '/app/controllers/ContactController.php';
require_once __DIR__ . '/app/controllers/AdminCatalogController.php';
require_once __DIR__ . '/app/controllers/AdminController.php';

// Obtener la ruta solicitada relativa al directorio base del proyecto en XAMPP
$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$basePath = BASE_URL;

if ($basePath !== '' && str_starts_with($requestUri, $basePath)) {
    $route = substr($requestUri, strlen($basePath));
} else {
    $route = $requestUri;
}

// Normalizar la ruta (ej. '/nosotros/' -> '/nosotros', '' -> '/')
$route = '/' . trim((string) $route, '/');
if ($route === '/index.php') {
    $route = '/';
}

// Mapa oficial de las 7 rutas del proyecto Hospital San Marcos
$routes = [
    '/' => [
        'view'        => 'home',
        'title'       => 'Hospital San Marcos - Pasaje, El Oro',
        'breadcrumb'  => 'Inicio',
        'description' => 'Sitio web institucional del Hospital San Marcos en Pasaje, El Oro. Atención médica humana, especialidades, servicios clínicos y emergencias 24 horas.',
    ],
    '/nosotros' => [
        'view'        => 'nosotros',
        'title'       => 'Nosotros | Hospital San Marcos - Pasaje, El Oro',
        'breadcrumb'  => 'Nosotros',
        'description' => 'Conoce la historia desde 2007, misión, visión, valores e infraestructura médica del Hospital San Marcos en Pasaje.',
    ],
    '/especialidades' => [
        'view'        => 'especialidades',
        'title'       => 'Especialidades Médicas | Hospital San Marcos - Pasaje',
        'breadcrumb'  => 'Especialidades',
        'description' => 'Cartera de 17 especialidades médicas acreditadas en el Hospital San Marcos de Pasaje, El Oro.',
    ],
    '/servicios' => [
        'view'        => 'servicios',
        'title'       => 'Servicios Clínicos y Hospitalarios | Hospital San Marcos',
        'breadcrumb'  => 'Servicios',
        'description' => 'Emergencias 24 horas, consulta externa, cirugía general y laparoscópica, ginecología, laboratorio clínico, imagenología y hospitalización.',
    ],
    '/equipo-medico' => [
        'view'        => 'equipo-medico',
        'title'       => 'Equipo Médico | Hospital San Marcos - Pasaje',
        'breadcrumb'  => 'Equipo médico',
        'description' => 'Directorio de médicos especialistas y profesionales de la salud del Hospital San Marcos en Pasaje, El Oro.',
    ],
    '/contacto' => [
        'view'        => 'contacto',
        'title'       => 'Contacto y Ubicación | Hospital San Marcos - Pasaje',
        'breadcrumb'  => 'Contacto',
        'description' => 'Comunícate con el Hospital San Marcos en Av. Quito y Sucre, Pasaje, El Oro. Teléfono y emergencias 24 horas: 098 797 3511.',
    ],
    '/agendar-cita' => [
        'view'        => 'agendar-cita',
        'title'       => 'Agendar Cita Médica | Hospital San Marcos - Pasaje',
        'breadcrumb'  => 'Agendar cita',
        'description' => 'Coordina tu consulta médica externa con los especialistas del Hospital San Marcos en Pasaje, El Oro.',
    ],
];

$requestMethod = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));

// Enrutamiento del Panel Administrativo (/admin y subrutas)
if ($route === '/admin' || str_starts_with($route, '/admin/')) {
    try {
        AdminController::dispatch($route, $requestMethod);
        exit;
    } catch (Throwable $e) {
        if (!($e instanceof DataSourceException)) {
            error_log('[HSM][ADMIN] ' . $e->getMessage());
        }
        http_response_code(503);
        exit('Servicio temporalmente no disponible. Intente nuevamente en unos minutos.');
    }
}

// Redireccionar cualquier ruta no registrada hacia la página de inicio
if (!array_key_exists($route, $routes)) {
    header('Location: ' . url('/'), true, 302);
    exit;
}

$currentRoute = $route;
$pageMeta     = $routes[$currentRoute];
$viewFile     = __DIR__ . '/views/pages/' . $pageMeta['view'] . '.php';

// Precarga de datos desde MySQL: si la base de datos falla, se muestra un error controlado
// (el detalle técnico queda solo en el log del servidor).
try {
    DoctorRepository::getAll();
    SpecialtyRepository::getAll();
    ServiceRepository::getAll();
    FaqRepository::getAll();
} catch (Throwable $e) {
    if (!($e instanceof DataSourceException)) {
        error_log('[HSM] ' . $e->getMessage());
    }
    http_response_code(503);
    header('Retry-After: 120');
    ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex">
  <title>Servicio no disponible | Hospital San Marcos</title>
  <link rel="stylesheet" href="<?= e(asset('css/styles.css')) ?>">
</head>
<body class="bg-surface text-on-surface font-body-md">
  <main class="min-h-screen flex items-center justify-center px-gutter">
    <div class="max-w-xl text-center bg-surface-container-lowest p-space-xl rounded-2xl shadow-md border border-outline-variant">
      <h1 class="font-headline-md text-headline-md text-primary mb-space-md">Estamos realizando mantenimiento</h1>
      <p class="font-body-lg text-body-lg text-on-surface-variant mb-space-lg">
        En este momento no podemos mostrar la información. Por favor, inténtalo de nuevo en unos minutos.
      </p>
      <p class="font-body-md text-body-md text-on-surface">
        Emergencias 24 horas: <a class="link-primary" href="<?= e(HOSPITAL_PHONE_LINK) ?>"><?= e(HOSPITAL_PHONE_DISPLAY) ?></a>
      </p>
    </div>
  </main>
</body>
</html>
    <?php
    exit;
}

// Procesar envíos POST e inicializar sesión en rutas con formularios interactivos
$formFeedback = null;

if ($currentRoute === '/agendar-cita') {
    Security::ensureSession();
    if ($requestMethod === 'POST') {
        $formFeedback = AppointmentController::handlePost();
    }
} elseif ($currentRoute === '/contacto') {
    Security::ensureSession();
    if ($requestMethod === 'POST') {
        $formFeedback = ContactController::handlePost();
    }
}

// Renderizar el Layout principal
require __DIR__ . '/views/layouts/app.php';
