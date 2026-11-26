# Hospital San Marcos — Portal Web Institucional (PHP 8.2 + XAMPP)

Sitio web institucional e informativo del **Hospital San Marcos** (Pasaje, Provincia de El Oro, Ecuador). Esta aplicación fue migrada a una arquitectura modular en **PHP 8.2 puro, HTML5 semántico, Tailwind CSS v4 compilado y JavaScript ES Modules**, ejecutándose de forma nativa sobre **Apache en XAMPP** sin depender de React ni de un servidor de desarrollo Node.js en tiempo de ejecución.

> **Alcance de la Versión Actual (Etapa 1 — Institucional e Informativa):**
> Esta primera versión comprende el portal institucional completo con sus 7 páginas, catálogos dinámicos servidos desde repositorios PHP desacoplados, buscadores en tiempo real, filtros por categorías, modales, pestañas interactivas y acordeones. Las funcionalidades transaccionales (almacenamiento en MySQL, agendamiento real de citas, envío de formulario de contacto, chatbot con IA y panel administrativo) están proyectadas para la **Etapa 2**, manteniéndose sus interfaces visuales con el envío final deshabilitado y avisos informativos claros para el paciente.

---

## 1. Tecnologías Utilizadas

- **Backend y Renderizado:** PHP 8.2+ (`declare(strict_types=1);`) con arquitectura de Controlador Frontal (`index.php`), vistas parciales reutilizables y patrón Repositorio.
- **Servidor Web:** Apache HTTP Server 2.4 (incluido en XAMPP para Windows) con `mod_rewrite` habilitado para URLs amigables.
- **Estilos y Diseño Visual:** HTML5 semántico + **Tailwind CSS v4** (`v4.3.3`) compilado a `public/css/styles.css`, conservando la paleta clínica institucional (`#006875`, `#2fb0c3`, `#416900`, `#c0001b`, `#f3fbff`), tipografías (`Newsreader`, `Plus Jakarta Sans`, `Figtree`), iconografía (`Material Symbols Outlined`) y logotipo vectorial SVG.
- **Interactividad en Cliente:** JavaScript moderno nativo (**ES6 Modules** en `public/js/`) sin frameworks ni librerías externas.

---

## 2. Requisitos de Instalación

1. **Sistema Operativo:** Windows 10 / 11.
2. **XAMPP para Windows** con:
   - **Apache 2.4+** con el módulo `mod_rewrite` activo (habilitado por defecto en XAMPP).
   - **PHP 8.2+**.
3. **Navegador Web moderno** (Chrome, Edge, Firefox, Safari).
4. *(Opcional, solo si se desea recompilar hojas de estilo)* **Node.js 18+** para ejecutar el compilador `@tailwindcss/cli`.

---

## 3. Estructura de Carpetas

```text
C:\xampp\htdocs\hospital-san-marcos\
├── .htaccess                         # Reglas de mod_rewrite hacia index.php y seguridad HTTP
├── index.php                         # Controlador Frontal (Front Controller) y enrutador de las 7 páginas
├── README.md                         # Documentación técnica y operativa del proyecto
├── config/
│   ├── .htaccess                     # Bloqueo de acceso HTTP directo (403 Forbidden)
│   ├── config.php                    # Constantes institucionales y helpers (url, asset, e, sanitize_query_param)
│   └── database.php                  # Clase PDO preparada para la futura integración con MySQL en Etapa 2
├── app/
│   ├── .htaccess                     # Bloqueo de acceso HTTP directo (403 Forbidden)
│   ├── data/                         # Catálogos estructurados en arreglos asociativos PHP
│   │   ├── doctors.php               # 29 médicos (22 acreditados + 7 con disponibilidad por confirmar)
│   │   ├── specialties.php           # 17 especialidades médicas
│   │   ├── services.php              # 6 servicios hospitalarios + 4 fichas operativas detalladas
│   │   └── faqs.php                  # 5 preguntas frecuentes institucionales
│   └── repositories/                 # Capa de acceso a datos desacoplada de las vistas
│       ├── DoctorRepository.php
│       ├── SpecialtyRepository.php
│       ├── ServiceRepository.php
│       └── FaqRepository.php
├── database/
│   └── .htaccess                     # Directorio reservado para migraciones SQL en Etapa 2 (403 Forbidden)
├── public/                           # Recursos estáticos públicos
│   ├── css/
│   │   ├── input.css                 # Archivo fuente de Tailwind CSS v4 y tokens de diseño
│   │   └── styles.css                # Hoja de estilos compilada lista para producción
│   └── js/
│       ├── app.js                    # Punto de entrada principal (<script type="module">)
│       ├── utils/
│       │   └── normalize.js          # Normalización NFD sin tildes ni distinción de mayúsculas
│       └── modules/
│           ├── navbar.js             # Menú móvil desplegable
│           ├── especialidades.js     # Buscador en tiempo real y 6 filtros de Especialidades
│           ├── equipo-medico.js      # Buscador, 6 pestañas de categorías, ?search= y modal de perfil médico
│           ├── servicios.js          # 4 pestañas operativas, smooth scroll y acordeón de 5 FAQs
│           ├── agendar-cita.js       # Flujo visual de 4 pasos, resumen en vivo y Asistente San Marcos
│           └── contacto.js           # Validaciones visuales y bloqueo de envío del formulario
└── views/
    ├── .htaccess                     # Bloqueo de acceso HTTP directo a las plantillas (403 Forbidden)
    ├── layouts/
    │   └── app.php                   # Layout maestro HTML5
    ├── partials/
    │   ├── head.php                  # Metadatos SEO, OpenGraph, Google Fonts y CSS
    │   ├── navbar.php                # Barra superior de contacto, logo SVG, menú escritorio/móvil
    │   ├── footer.php                # Pie de página institucional de 4 columnas
    │   ├── breadcrumb.php            # Migas de pan dinámicas
    │   └── whatsapp-float.php        # Botón flotante de contacto rápido por WhatsApp
    └── pages/
        ├── home.php                  # Página de Inicio
        ├── nosotros.php              # Página Nosotros
        ├── servicios.php             # Página Servicios
        ├── especialidades.php        # Página Especialidades
        ├── equipo-medico.php         # Página Equipo Médico
        ├── contacto.php              # Página Contacto
        └── agendar-cita.php          # Página Agendar Cita + Asistente San Marcos
```

---

## 4. Instrucciones para Ejecutar el Proyecto en XAMPP

1. Verifica que la carpeta del proyecto se encuentre en:
   ```text
   C:\xampp\htdocs\hospital-san-marcos
   ```
2. Abre el **XAMPP Control Panel** (`C:\xampp\xampp-control.exe`).
3. Inicia únicamente el módulo **Apache** haciendo clic en **Start** (no es necesario iniciar MySQL en esta Etapa 1).
4. Abre tu navegador web e ingresa a:
   ```text
   http://localhost/hospital-san-marcos/
   ```

---

## 5. URLs de las Siete Páginas

| Página | URL en XAMPP | Descripción |
| :--- | :--- | :--- |
| **Inicio** | `http://localhost/hospital-san-marcos/` | Portada principal, métricas, servicios destacados, valores y llamados a la acción. |
| **Nosotros** | `http://localhost/hospital-san-marcos/nosotros` | Historia desde 2007, misión, visión, 6 valores institucionales e infraestructura. |
| **Servicios** | `http://localhost/hospital-san-marcos/servicios` | 6 servicios clínicos, 4 fichas operativas interactivas por pestañas y 5 preguntas frecuentes. |
| **Especialidades** | `http://localhost/hospital-san-marcos/especialidades` | Cartera de 17 especialidades médicas con buscador en tiempo real y 6 filtros de categoría. |
| **Equipo Médico** | `http://localhost/hospital-san-marcos/equipo-medico` | Directorio de 29 profesionales con buscador, 6 categorías, soporte `?search=` y modal de perfil. |
| **Contacto** | `http://localhost/hospital-san-marcos/contacto` | Tarjetas de contacto rápido, formulario (envío deshabilitado), mapa, rutas y redes oficiales. |
| **Agendar Cita** | `http://localhost/hospital-san-marcos/agendar-cita` | Flujo interactivo de 4 pasos (confirmación deshabilitada), soporte `?doctor=` y Asistente San Marcos. |

---

## 6. Explicación de los Repositorios PHP

Para desacoplar las vistas HTML de la fuente de datos y facilitar la futura migración a MySQL sin reescribir las plantillas, el proyecto implementa cuatro clases de repositorio en `app/repositories/`:

1. **`DoctorRepository` (`app/repositories/DoctorRepository.php`):**
   - `getAll()`: Retorna los 29 médicos desde `app/data/doctors.php`.
   - `findById(string $id)`: Busca un médico por su identificador único (`slug`).
   - `getCategoriesWithCounts()`: Calcula dinámicamente los conteos de las 6 categorías (`Todos (29)`, `Cirugía y áreas quirúrgicas (7)`, `Ginecología (3)`, `Medicina interna y clínica (9)`, `Traumatología (3)`, `Otras especialidades (7)`).
   - `getCategoryBadgeLabel(array $doctor)`: Determina la etiqueta clínica de cada tarjeta.

2. **`SpecialtyRepository` (`app/repositories/SpecialtyRepository.php`):**
   - `getAll()`: Retorna las 17 especialidades médicas desde `app/data/specialties.php`.
   - `findById(string $id)`: Busca una especialidad por su identificador.
   - `getFilterCategories()`: Retorna las 6 categorías de filtrado clínico.

3. **`ServiceRepository` (`app/repositories/ServiceRepository.php`):**
   - `getAll()`: Retorna los 6 servicios hospitalarios principales desde `app/data/services.php`.
   - `getDetailsMap()` y `getDetailByKey(string $key)`: Retornan las 4 fichas operativas detalladas (`laboratorio`, `imagen`, `cirugia`, `hospitalizacion`).

4. **`FaqRepository` (`app/repositories/FaqRepository.php`):**
   - `getAll()`: Retorna las 5 preguntas frecuentes desde `app/data/faqs.php`.

---

## 7. Cómo Modificar los Datos de Médicos, Especialidades y Servicios

- **Agregar o editar un médico:** Edita `app/data/doctors.php`. Cada elemento del arreglo contiene `id`, `name`, `specialty`, `category` (`cirugia`, `ginecologia`, `medicina-interna`, `traumatologia` u `otras`), `initials`, `location`, `accredited` (`true` o `false`) y `description`. Los conteos de filtros y el selector de Agendar Cita se actualizarán automáticamente.
- **Agregar o editar una especialidad:** Edita `app/data/specialties.php`. Cada especialidad incluye `id`, `name`, `category` (cadena separada por espacios con las categorías aplicables), `icon`, `badgeType` (`primary`, `secondary` o `tertiary`), `badgeText`, `description` y `doctorSearchQuery`.
- **Agregar o editar servicios o fichas operativas:** Edita `app/data/services.php` en las claves `'services'` (cuadrícula de 6 tarjetas) o `'details'` (fichas operativas por pestaña).
- **Agregar o editar preguntas frecuentes:** Edita `app/data/faqs.php` modificando `id`, `question` o `answer`.

---

## 8. Cómo Recompilar Tailwind CSS

El archivo CSS compilado (`public/css/styles.css`) ya incluye todos los estilos necesarios para ejecutar el proyecto. Si en el futuro modificas clases de Tailwind en `views/` o `public/js/`, puedes recompilar `public/css/styles.css` a partir de `public/css/input.css`:

1. Instala `tailwindcss` y `@tailwindcss/cli` en un entorno Node local o temporal:
   ```powershell
   npm install -D tailwindcss @tailwindcss/cli
   ```
2. Ejecuta el compilador apuntando a `public/css/input.css`:
   ```powershell
   npx @tailwindcss/cli -i "C:\xampp\htdocs\hospital-san-marcos\public\css\input.css" -o "C:\xampp\htdocs\hospital-san-marcos\public\css\styles.css"
   ```

---

## 9. Funcionalidades Implementadas (Etapa 1)

- **Diseño visual 100% fiel al original:** Colores institucionales, tipografías, logotipo SVG, tarjetas, iconografía, animaciones y diseño responsive (móvil, tablet y escritorio).
- **Enrutamiento limpio por Front Controller:** Soporte para las 7 rutas oficiales y redirección automática de rutas inexistentes hacia Inicio (`/`).
- **Menú de navegación móvil interactivo:** Apertura/cierre con actualización de icono y atributos de accesibilidad (`aria-expanded`).
- **Página Especialidades interactiva:** Buscador en tiempo real insensible a mayúsculas y tildes, 6 filtros de categorías, contador dinámico y estado vacío con botón de restablecimiento.
- **Página Equipo Médico interactiva:** Buscador por nombre/especialidad, 6 pestañas con conteos dinámicos, soporte de parámetro `?search=` con equivalencias clínicas y modal de perfil médico (cierre por botón, tecla `Escape` o clic fuera).
- **Página Servicios interactiva:** 4 pestañas de fichas operativas, desplazamiento suave (*smooth scroll*) desde las tarjetas de servicios y acordeón de 5 preguntas frecuentes.
- **Flujo visual de Agendar Cita y Asistente San Marcos:** Navegación interactiva entre los 4 pasos, barra de progreso, selector visual de turno, resumen dinámico en el Paso 4, preselección validada de médico mediante `?doctor=`, botón final deshabilitado y aviso de disponibilidad próxima.
- **Página Contacto:** Diseño completo, validaciones visuales en cliente, botón de envío deshabilitado y aviso de disponibilidad próxima.
- **Seguridad reforzada:** Protección `.htaccess` (`403 Forbidden`) y constante `HSM_APP` en directorios internos (`app/`, `config/`, `database/`, `views/`), escape sistemático XSS con `e()`, validación de parámetros URL (`sanitize_query_param()`) y enlaces de WhatsApp libres de datos personales o médicos del paciente.

---

## 10. Funcionalidades Pendientes (Etapa 2)

1. Creación e integración de la base de datos MySQL/MariaDB (`hospital_san_marcos`).
2. Procesamiento y almacenamiento real de solicitudes en **Agendar Cita** y gestión de horarios/disponibilidad médica.
3. Procesamiento y almacenamiento/envío de mensajes del formulario de **Contacto**.
4. Integración funcional del chatbot **Asistente San Marcos**.
5. Sistema de autenticación y **Panel Administrativo** para gestionar médicos, especialidades, servicios, citas y mensajes de contacto.

---

## 11. Recomendaciones para Implementar Posteriormente MySQL, Citas, Chatbot y Panel Administrativo

1. **Migración transparente a MySQL mediante los Repositorios:**
   - Activa `config/database.php` (que ya cuenta con conexión PDO configurada con `ERRMODE_EXCEPTION` y `ATTR_EMULATE_PREPARES => false`).
   - Crea las tablas `specialties`, `doctors`, `services`, `service_details`, `faqs`, `appointments` y `contact_messages` en la base de datos `hospital_san_marcos`.
   - Reemplaza internamente el contenido de `DoctorRepository::getAll()`, `SpecialtyRepository::getAll()`, `ServiceRepository::getAll()` y `FaqRepository::getAll()` por consultas PDO `SELECT`. **No tendrás que modificar ninguna vista en `views/pages/`**.
2. **Agendamiento de Citas y Formulario de Contacto:**
   - Crea controladores POST dedicados con protección **CSRF Token** en sesión (`$_SESSION['csrf_token']`), validación estricta en servidor y consultas preparadas `INSERT INTO appointments` / `INSERT INTO contact_messages`.
3. **Asistente San Marcos (Chatbot):**
   - Implementa un endpoint backend en PHP que reciba la consulta del usuario y consulte un árbol de decisión local o una API de IA desde el servidor (manteniendo cualquier API Key en variables de entorno fuera del navegador).
4. **Panel Administrativo:**
   - Implementa autenticación con `password_hash()` / `password_verify()`, regeneración de sesión (`session_regenerate_id(true)`) y control de acceso basado en roles para administrar los catálogos hospitalarios.
