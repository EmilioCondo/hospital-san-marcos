<?php
declare(strict_types=1);

if (!defined('HSM_APP')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

$currentPage = 'Nosotros';
?>
<div class="hospital-page-hero flex flex-col w-full">
  <!-- HERO INSTITUCIONAL -->
  <section class="relative w-full bg-gradient-to-b from-surface-container-low via-surface to-surface overflow-hidden pt-8 pb-space-2xl">
    <div class="absolute -top-32 -right-32 w-96 h-96 rounded-full bg-primary-container/10 blur-3xl pointer-events-none"></div>
    <div class="absolute top-1/2 -left-20 w-80 h-80 rounded-full bg-secondary-container/15 blur-3xl pointer-events-none"></div>
    <div class="max-w-7xl mx-auto px-gutter relative z-10">
      <!-- Breadcrumb -->
      <?php require __DIR__ . '/../partials/breadcrumb.php'; ?>

      <div class="hospital-hero-grid grid grid-cols-1 lg:grid-cols-12 gap-space-xl items-start">
        <div class="hospital-hero-copy lg:col-span-8 space-y-space-md">
          <h1 class="hospital-hero-title font-headline-lg text-display-mobile md:text-display text-on-surface tracking-tight leading-[1.1]">
            Conoce nuestra historia y compromiso con tu salud.
          </h1>
          <p class="hospital-hero-description font-body-lg text-body-lg text-on-surface-variant max-w-2xl leading-relaxed">
            Trayectoria, vocación de servicio y atención médica de calidad para las familias de Pasaje y la provincia de El Oro.
          </p>
          <div class="pt-space-sm flex flex-wrap items-center gap-space-md text-label-md font-label-md">
            <div class="flex items-center gap-2 bg-surface-container-lowest px-4 py-2.5 rounded-xl shadow-sm text-on-surface border border-outline-variant">
              <span class="material-symbols-outlined text-primary text-[20px]">verified</span>
              <span>Segundo Nivel Asistencial</span>
            </div>
            <div class="flex items-center gap-2 bg-surface-container-lowest px-4 py-2.5 rounded-xl shadow-sm text-on-surface border border-outline-variant">
              <span class="material-symbols-outlined text-secondary text-[20px]">award_star</span>
              <span>Atención Integral y Docente</span>
            </div>
          </div>
        </div>

        <div class="lg:col-span-4 relative">
          <div class="relative rounded-2xl overflow-hidden shadow-xl bg-surface-container-lowest p-2 border border-outline-variant">
            <img
              class="w-full h-80 object-cover rounded-xl"
              alt="Instalaciones y fachada moderna de Hospital San Marcos en Pasaje"
              src="img\Nosotros.jpg"
            />
            <div class="absolute bottom-4 left-4 right-4 bg-inverse-surface/90 backdrop-blur-md text-surface p-space-md rounded-xl shadow-md">
              <p class="font-label-sm text-label-sm uppercase tracking-wider text-primary-fixed mb-0.5">
                Sede Pasaje, El Oro
              </p>
              <p class="font-title-md text-title-md font-semibold">Salud humana y vanguardia técnica</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- QUIÉNES SOMOS -->
  <section class="w-full bg-surface-container-lowest py-space-2xl border-y border-outline-variant">
    <div class="max-w-7xl mx-auto px-gutter">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-space-xl items-center">
        <!-- Visual side -->
        <div class="lg:col-span-5 relative order-2 lg:order-1">
          <div class="rounded-2xl overflow-hidden shadow-md bg-surface-container-low p-2 border border-outline-variant">
            <img
              class="w-full h-96 object-cover rounded-xl"
              alt="Médico de Hospital San Marcos atendiendo a un paciente con cercanía y calidez humana"
              src="img\CuidadoCentrado1.png"
            />
          </div>
          <!-- Floating highlight card -->
          <div class="absolute -bottom-6 -right-4 md:right-4 bg-primary text-on-primary p-space-lg rounded-2xl shadow-xl max-w-xs space-y-2">
            <div class="flex items-center gap-2">
              <span class="material-symbols-outlined text-primary-fixed text-[24px]">favorite</span>
              <span class="font-title-md text-title-md font-bold">Cuidado Centrado</span>
            </div>
            <p class="font-body-sm text-body-sm text-on-primary/90">
              Atención cercana que comprende la realidad y el bienestar de cada hogar orense.
            </p>
          </div>
        </div>

        <!-- Copy side -->
        <div class="lg:col-span-7 space-y-space-md order-1 lg:order-2">
          <span class="font-label-sm text-label-sm uppercase tracking-widest text-primary font-bold block">
            QUIÉNES SOMOS
          </span>
          <h2 class="font-headline-lg text-headline-md md:text-headline-lg text-on-surface">
            Excelencia asistencial, calidez en el trato y profundo arraigo local.
          </h2>
          <div class="space-y-space-md font-body-md text-body-md text-on-surface-variant leading-relaxed">
            <p>
              El <strong class="text-on-surface font-semibold">Hospital San Marcos</strong> se erige como una institución de salud de referencia en el cantón Pasaje, cimentada sobre la firme convicción de brindar servicios médicos que equilibren la rigurosidad científica con un trato cálido y profundamente humano.
            </p>
            <p>
              Entendemos que la recuperación y tranquilidad de nuestros pacientes se construyen tanto en quirófanos y laboratorios de alta precisión como en el acompañamiento respetuoso y la empatía de nuestro personal. Por ello, articulamos especialistas certificados, infraestructura segura y protocolos clínicos vigentes para responder oportunamente a cada etapa del ciclo de vida.
            </p>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-space-md pt-space-sm">
            <div class="p-space-md rounded-xl bg-surface-container-low text-on-surface border border-outline-variant">
              <span class="font-headline-sm text-headline-sm text-primary font-bold block">24/7</span>
              <span class="font-body-sm text-body-sm text-on-surface-variant block mt-1">
                Servicio de emergencias permanente
              </span>
            </div>
            <div class="p-space-md rounded-xl bg-surface-container-low text-on-surface border border-outline-variant">
              <span class="font-headline-sm text-headline-sm text-primary font-bold block">100%</span>
              <span class="font-body-sm text-body-sm text-on-surface-variant block mt-1">
                Enfoque ético y personalizado
              </span>
            </div>
            <div class="p-space-md rounded-xl bg-surface-container-low text-on-surface border border-outline-variant">
              <span class="font-headline-sm text-headline-sm text-primary font-bold block">Nivel II</span>
              <span class="font-body-sm text-body-sm text-on-surface-variant block mt-1">
                Complejidad asistencial y quirúrgica
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- HISTORIA INSTITUCIONAL (LÍNEA DE TIEMPO) -->
  <section class="w-full bg-surface py-space-2xl">
    <div class="max-w-7xl mx-auto px-gutter space-y-space-2xl">
      <div class="text-center max-w-2xl mx-auto space-y-space-xs">
        <span class="font-label-sm text-label-sm uppercase tracking-widest text-primary font-bold block">
          MEMORIA Y EVOLUCIÓN
        </span>
        <h2 class="font-headline-lg text-headline-md md:text-headline-lg text-on-surface">
          Nuestra historia institucional
        </h2>
        <p class="font-body-md text-body-md text-on-surface-variant">
          Un trayecto continuo de dedicación y ampliación al servicio de la salud en Pasaje y El Oro.
        </p>
      </div>

      <!-- Timeline container -->
      <div class="relative max-w-4xl mx-auto">
        <div class="hidden md:block absolute left-1/2 top-4 bottom-4 w-1 bg-surface-container-high -translate-x-1/2 rounded-full"></div>
        <div class="space-y-space-xl relative">
          <!-- Hito 1: 2007 -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-space-lg items-center relative">
            <div class="md:text-right space-y-space-xs md:pr-space-xl">
              <span class="inline-block px-3 py-1 rounded-full bg-primary-container text-on-primary font-label-md text-label-md font-bold tracking-wider">
                AÑO 2007
              </span>
              <h3 class="font-title-lg text-title-lg text-on-surface font-bold">Inauguración Fundacional</h3>
              <p class="font-body-md text-body-md text-on-surface-variant">
                Inauguración de la Clínica San Marcos con laboratorio clínico y consultorios médicos especializados en Pasaje.
              </p>
            </div>
            <div class="hidden md:flex absolute left-1/2 -translate-x-1/2 w-10 h-10 rounded-full bg-primary text-on-primary items-center justify-center shadow-md">
              <span class="material-symbols-outlined text-[20px]">flag</span>
            </div>
            <div class="md:pl-space-xl">
              <div class="p-space-lg rounded-2xl bg-surface-container-lowest shadow-sm flex items-center gap-space-md border border-outline-variant">
                <div class="w-12 h-12 rounded-xl bg-surface-container-low text-primary flex items-center justify-center shrink-0">
                  <span class="material-symbols-outlined text-[26px]">biotech</span>
                </div>
                <p class="font-body-sm text-body-sm text-on-surface-variant">
                  Apertura con servicios diagnósticos iniciales y atención en consultas médicas para las familias pasajeñas.
                </p>
              </div>
            </div>
          </div>

          <!-- Hito 2: Etapa de Crecimiento -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-space-lg items-center relative">
            <div class="order-2 md:order-1 md:text-right md:pr-space-xl">
              <div class="p-space-lg rounded-2xl bg-surface-container-lowest shadow-sm flex items-center gap-space-md justify-end text-left border border-outline-variant">
                <p class="font-body-sm text-body-sm text-on-surface-variant">
                  Incorporación de nuevas consultas especializadas, soporte diagnóstico integral y capacidad resolutiva ampliada.
                </p>
                <div class="w-12 h-12 rounded-xl bg-surface-container-low text-secondary flex items-center justify-center shrink-0">
                  <span class="material-symbols-outlined text-[26px]">trending_up</span>
                </div>
              </div>
            </div>
            <div class="hidden md:flex absolute left-1/2 -translate-x-1/2 w-10 h-10 rounded-full bg-secondary text-on-secondary items-center justify-center shadow-md">
              <span class="material-symbols-outlined text-[20px]">domain_add</span>
            </div>
            <div class="order-1 md:order-2 space-y-space-xs md:pl-space-xl">
              <span class="inline-block px-3 py-1 rounded-full bg-secondary-container text-on-secondary-container font-label-md text-label-md font-bold tracking-wider">
                EXPANSIÓN SOSTENIDA
              </span>
              <h3 class="font-title-lg text-title-lg text-on-surface font-bold">Etapa de Crecimiento</h3>
              <p class="font-body-md text-body-md text-on-surface-variant">
                Ampliación continua de servicios médicos, incorporación de nuevas especialidades y diagnóstico para responder a las necesidades crecientes de la comunidad.
              </p>
            </div>
          </div>

          <!-- Hito 3: Evolución Hospitalaria -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-space-lg items-center relative">
            <div class="md:text-right space-y-space-xs md:pr-space-xl">
              <span class="inline-block px-3 py-1 rounded-full bg-surface-container-highest text-on-primary-container font-label-md text-label-md font-bold tracking-wider">
                SEGUNDO NIVEL
              </span>
              <h3 class="font-title-lg text-title-lg text-on-surface font-bold">Evolución Hospitalaria</h3>
              <p class="font-body-md text-body-md text-on-surface-variant">
                Desarrollo hacia la atención hospitalaria de segundo nivel con hospitalización, centro quirúrgico e imagenología integral.
              </p>
            </div>
            <div class="hidden md:flex absolute left-1/2 -translate-x-1/2 w-10 h-10 rounded-full bg-primary text-on-primary items-center justify-center shadow-md">
              <span class="material-symbols-outlined text-[20px]">local_hospital</span>
            </div>
            <div class="md:pl-space-xl">
              <div class="p-space-lg rounded-2xl bg-surface-container-lowest shadow-sm flex items-center gap-space-md border border-outline-variant">
                <div class="w-12 h-12 rounded-xl bg-surface-container-low text-primary flex items-center justify-center shrink-0">
                  <span class="material-symbols-outlined text-[26px]">medical_services</span>
                </div>
                <p class="font-body-sm text-body-sm text-on-surface-variant">
                  Infraestructura completa con quirófano, áreas de recuperación y tecnologías para diagnóstico por imágenes.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- MISIÓN Y VISIÓN -->
  <section class="w-full bg-surface-container-lowest py-space-2xl border-y border-outline-variant">
    <div class="max-w-7xl mx-auto px-gutter space-y-space-xl">
      <div class="max-w-2xl space-y-space-xs">
        <span class="font-label-sm text-label-sm uppercase tracking-widest text-primary font-bold block">
          PROPÓSITO INSTITUCIONAL
        </span>
        <h2 class="font-headline-lg text-headline-md md:text-headline-lg text-on-surface">
          Misión y Visión
        </h2>
        <p class="font-body-md text-body-md text-on-surface-variant">
          Los pilares rectores que orientan nuestro quehacer clínico, docente y comunitario.
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-space-xl">
        <!-- Tarjeta Misión -->
        <div class="relative bg-surface-container-low p-space-xl rounded-2xl shadow-sm flex flex-col justify-between overflow-hidden border border-outline-variant">
          <div class="space-y-space-md">
            <div class="flex items-center justify-between">
              <div class="w-14 h-14 rounded-2xl bg-primary-container text-on-primary flex items-center justify-center shadow-md">
                <span class="material-symbols-outlined text-[32px]">assignment_turned_in</span>
              </div>
              <span class="font-label-sm text-label-sm tracking-widest uppercase font-bold text-primary">
                MISIÓN
              </span>
            </div>
            <h3 class="font-headline-sm text-headline-sm text-on-surface">Nuestra Misión</h3>
            <p class="font-body-lg text-body-lg text-on-surface-variant leading-relaxed">
              “Ofrecer atención médica de calidad mediante profesionales capacitados, tecnología y compromiso con el bienestar de los pacientes, incluyendo la formación de profesionales de la salud.”
            </p>
          </div>
          <div class="mt-space-lg pt-space-md flex items-center gap-space-sm text-label-sm font-label-sm text-primary">
            <span class="material-symbols-outlined text-[18px]">school</span>
            <span>Compromiso asistencial y vocación formadora</span>
          </div>
        </div>

        <!-- Tarjeta Visión -->
        <div class="relative bg-surface-container-high/60 p-space-xl rounded-2xl shadow-sm flex flex-col justify-between overflow-hidden border border-outline-variant">
          <div class="space-y-space-md">
            <div class="flex items-center justify-between">
              <div class="w-14 h-14 rounded-2xl bg-secondary text-on-secondary flex items-center justify-center shadow-md">
                <span class="material-symbols-outlined text-[32px]">visibility</span>
              </div>
              <span class="font-label-sm text-label-sm tracking-widest uppercase font-bold text-secondary">
                VISIÓN
              </span>
            </div>
            <h3 class="font-headline-sm text-headline-sm text-on-surface">Nuestra Visión</h3>
            <p class="font-body-lg text-body-lg text-on-surface-variant leading-relaxed">
              “Convertirse en una institución de referencia por la excelencia en los servicios médicos y la enseñanza de la medicina, brindando atención eficaz, oportuna y confiable.”
            </p>
          </div>
          <div class="mt-space-lg pt-space-md flex items-center gap-space-sm text-label-sm font-label-sm text-secondary">
            <span class="material-symbols-outlined text-[18px]">verified</span>
            <span>Referente regional en eficacia y confiabilidad médica</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- VALORES INSTITUCIONALES -->
  <section class="w-full bg-surface py-space-2xl">
    <div class="max-w-7xl mx-auto px-gutter space-y-space-xl">
      <div class="text-center max-w-2xl mx-auto space-y-space-xs">
        <span class="font-label-sm text-label-sm uppercase tracking-widest text-primary font-bold block">
          PRINCIPIOS ÉTICOS
        </span>
        <h2 class="font-headline-lg text-headline-md md:text-headline-lg text-on-surface">
          Nuestros Valores Institucionales
        </h2>
        <p class="font-body-md text-body-md text-on-surface-variant">
          Pautas innegociables que sustentan cada consulta, diagnóstico y procedimiento médico.
        </p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-space-lg">
        <!-- 1. Humanidad -->
        <div class="bg-surface-container-lowest p-space-lg rounded-2xl shadow-sm hover:shadow-md transition-shadow space-y-space-sm border border-outline-variant">
          <div class="w-12 h-12 rounded-xl bg-surface-container-low text-primary flex items-center justify-center">
            <span class="material-symbols-outlined text-[26px]">volunteer_activism</span>
          </div>
          <h3 class="font-title-lg text-title-lg text-on-surface font-bold">Humanidad</h3>
          <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
            Trato cálido, empático y centrado en la persona y sus seres queridos en todo momento.
          </p>
        </div>

        <!-- 2. Responsabilidad -->
        <div class="bg-surface-container-lowest p-space-lg rounded-2xl shadow-sm hover:shadow-md transition-shadow space-y-space-sm border border-outline-variant">
          <div class="w-12 h-12 rounded-xl bg-surface-container-low text-primary flex items-center justify-center">
            <span class="material-symbols-outlined text-[26px]">gavel</span>
          </div>
          <h3 class="font-title-lg text-title-lg text-on-surface font-bold">Responsabilidad</h3>
          <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
            Rigor profesional y estricto compromiso ético en cada decisión y acto médico practicado.
          </p>
        </div>

        <!-- 3. Profesionalismo -->
        <div class="bg-surface-container-lowest p-space-lg rounded-2xl shadow-sm hover:shadow-md transition-shadow space-y-space-sm border border-outline-variant">
          <div class="w-12 h-12 rounded-xl bg-surface-container-low text-primary flex items-center justify-center">
            <span class="material-symbols-outlined text-[26px]">medical_information</span>
          </div>
          <h3 class="font-title-lg text-title-lg text-on-surface font-bold">Profesionalismo</h3>
          <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
            Médicos altamente capacitados con permanente actualización técnica y académica.
          </p>
        </div>

        <!-- 4. Respeto -->
        <div class="bg-surface-container-lowest p-space-lg rounded-2xl shadow-sm hover:shadow-md transition-shadow space-y-space-sm border border-outline-variant">
          <div class="w-12 h-12 rounded-xl bg-surface-container-low text-secondary flex items-center justify-center">
            <span class="material-symbols-outlined text-[26px]">handshake</span>
          </div>
          <h3 class="font-title-lg text-title-lg text-on-surface font-bold">Respeto</h3>
          <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
            Reconocimiento profundo a la dignidad, privacidad y autonomía de cada paciente y su familia.
          </p>
        </div>

        <!-- 5. Compromiso -->
        <div class="bg-surface-container-lowest p-space-lg rounded-2xl shadow-sm hover:shadow-md transition-shadow space-y-space-sm border border-outline-variant">
          <div class="w-12 h-12 rounded-xl bg-surface-container-low text-secondary flex items-center justify-center">
            <span class="material-symbols-outlined text-[26px]">loyalty</span>
          </div>
          <h3 class="font-title-lg text-title-lg text-on-surface font-bold">Compromiso</h3>
          <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
            Vocación incondicional de servicio activo con la salud y bienestar integral de Pasaje.
          </p>
        </div>

        <!-- 6. Calidad de atención -->
        <div class="bg-surface-container-lowest p-space-lg rounded-2xl shadow-sm hover:shadow-md transition-shadow space-y-space-sm border border-outline-variant">
          <div class="w-12 h-12 rounded-xl bg-surface-container-low text-secondary flex items-center justify-center">
            <span class="material-symbols-outlined text-[26px]">health_and_safety</span>
          </div>
          <h3 class="font-title-lg text-title-lg text-on-surface font-bold">Calidad de atención</h3>
          <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
            Estándares rigurosos de bioseguridad, higiene estéril y óptima eficiencia asistencial.
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- INFRAESTRUCTURA Y TECNOLOGÍA MÉDICA -->
  <section class="w-full bg-surface-container-lowest py-space-2xl border-t border-outline-variant">
    <div class="max-w-7xl mx-auto px-gutter space-y-space-2xl">
      <div class="flex flex-col md:flex-row md:items-end justify-between gap-space-md">
        <div class="max-w-2xl space-y-space-xs">
          <span class="font-label-sm text-label-sm uppercase tracking-widest text-primary font-bold block">
            INSTALACIONES HOSPITALARIAS
          </span>
          <h2 class="font-headline-lg text-headline-md md:text-headline-lg text-on-surface">
            Infraestructura y tecnología médica
          </h2>
          <p class="font-body-md text-body-md text-on-surface-variant">
            Espacios diseñados conforme a normativas de seguridad clínica, bioseguridad y confort del paciente.
          </p>
        </div>
        <div class="hidden md:flex items-center gap-2 text-label-sm font-label-sm text-outline">
          <span class="material-symbols-outlined text-[18px]">verified_user</span>
          <span>Tecnología diagnóstica y quirúrgica de vanguardia</span>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-space-lg">
        <!-- 1. Laboratorio clínico -->
        <div class="bg-surface rounded-2xl overflow-hidden shadow-sm flex flex-col group border border-outline-variant">
          <div class="relative h-48 overflow-hidden bg-surface-container-low">
            <img
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
              alt="Laboratorio clínico automatizado en Hospital San Marcos Pasaje"
              src="img\LaboratorioClinico.png"
            />
            <div class="absolute top-3 left-3 bg-inverse-surface/80 text-surface text-label-sm font-label-sm px-2.5 py-1 rounded-full backdrop-blur-sm">
              Diagnóstico In Situ
            </div>
          </div>
          <div class="p-space-lg flex-1 flex flex-col justify-between space-y-space-sm">
            <div>
              <div class="flex items-center gap-2 text-primary font-bold text-title-md font-title-md mb-1">
                <span class="material-symbols-outlined text-[20px]">science</span>
                <span>Laboratorio Clínico</span>
              </div>
              <p class="font-body-sm text-body-sm text-on-surface-variant">
                Análisis automatizados de alta precisión bioquímica, hematológica e inmunológica para resultados confiables y oportunos.
              </p>
            </div>
            <div class="pt-2 text-label-sm font-label-sm text-primary flex items-center gap-1 font-semibold">
              <span>Procesamiento ágil 24/7</span>
            </div>
          </div>
        </div>

        <!-- 2. Rayos X -->
        <div class="bg-surface rounded-2xl overflow-hidden shadow-sm flex flex-col group border border-outline-variant">
          <div class="relative h-48 overflow-hidden bg-surface-container-low">
            <img
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
              alt="Suite de radiología digital en Hospital San Marcos"
              src="img\RayosX.png"
            />
            <div class="absolute top-3 left-3 bg-inverse-surface/80 text-surface text-label-sm font-label-sm px-2.5 py-1 rounded-full backdrop-blur-sm">
              Radiología Digital
            </div>
          </div>
          <div class="p-space-lg flex-1 flex flex-col justify-between space-y-space-sm">
            <div>
              <div class="flex items-center gap-2 text-primary font-bold text-title-md font-title-md mb-1">
                <span class="material-symbols-outlined text-[20px]">radiology</span>
                <span>Rayos X</span>
              </div>
              <p class="font-body-sm text-body-sm text-on-surface-variant">
                Radiología digital para diagnósticos rápidos con menor dosis de radiación y captura nítida de estructuras óseas y torácicas.
              </p>
            </div>
            <div class="pt-2 text-label-sm font-label-sm text-primary flex items-center gap-1 font-semibold">
              <span>Informes radiológicos inmediatos</span>
            </div>
          </div>
        </div>

        <!-- 3. Ecografías -->
        <div class="bg-surface rounded-2xl overflow-hidden shadow-sm flex flex-col group border border-outline-variant">
          <div class="relative h-48 overflow-hidden bg-surface-container-low">
            <img
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
              alt="Sala de ultrasonido y ecografías de alta resolución en Pasaje"
              src="img\Ecografias.png"
            />
            <div class="absolute top-3 left-3 bg-inverse-surface/80 text-surface text-label-sm font-label-sm px-2.5 py-1 rounded-full backdrop-blur-sm">
              Ultrasonido Especializado
            </div>
          </div>
          <div class="p-space-lg flex-1 flex flex-col justify-between space-y-space-sm">
            <div>
              <div class="flex items-center gap-2 text-primary font-bold text-title-md font-title-md mb-1">
                <span class="material-symbols-outlined text-[20px]">pregnant_woman</span>
                <span>Ecografías</span>
              </div>
              <p class="font-body-sm text-body-sm text-on-surface-variant">
                Ultrasonido general, obstétrico de alta resolución y estudios Doppler vascular para evaluación no invasiva exhaustiva.
              </p>
            </div>
            <div class="pt-2 text-label-sm font-label-sm text-primary flex items-center gap-1 font-semibold">
              <span>Doppler • Obstétrica • General</span>
            </div>
          </div>
        </div>

        <!-- 4. Tomografía -->
        <div class="bg-surface rounded-2xl overflow-hidden shadow-sm flex flex-col group border border-outline-variant">
          <div class="relative h-48 overflow-hidden bg-surface-container-low">
            <img
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
              alt="Tomografía computarizada en Hospital San Marcos Pasaje"
              src="img\Tomografia.png"
            />
            <div class="absolute top-3 left-3 bg-inverse-surface/80 text-surface text-label-sm font-label-sm px-2.5 py-1 rounded-full backdrop-blur-sm">
              Cortes Multicorte
            </div>
          </div>
          <div class="p-space-lg flex-1 flex flex-col justify-between space-y-space-sm">
            <div>
              <div class="flex items-center gap-2 text-primary font-bold text-title-md font-title-md mb-1">
                <span class="material-symbols-outlined text-[20px]">vital_signs</span>
                <span>Tomografía</span>
              </div>
              <p class="font-body-sm text-body-sm text-on-surface-variant">
                Estudios de imagen de alta resolución que posibilitan reconstrucciones anatómicas volumétricas para diagnósticos certeros.
              </p>
            </div>
            <div class="pt-2 text-label-sm font-label-sm text-primary flex items-center gap-1 font-semibold">
              <span>Resolución milimétrica</span>
            </div>
          </div>
        </div>

        <!-- 5. Quirófanos -->
        <div class="bg-surface rounded-2xl overflow-hidden shadow-sm flex flex-col group border border-outline-variant">
          <div class="relative h-48 overflow-hidden bg-surface-container-low">
            <img
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
              alt="Quirófano estéril moderno en Hospital San Marcos"
              src="img\Quirofanos1.png"
            />
            <div class="absolute top-3 left-3 bg-inverse-surface/80 text-surface text-label-sm font-label-sm px-2.5 py-1 rounded-full backdrop-blur-sm">
              Cirugía Segura
            </div>
          </div>
          <div class="p-space-lg flex-1 flex flex-col justify-between space-y-space-sm">
            <div>
              <div class="flex items-center gap-2 text-primary font-bold text-title-md font-title-md mb-1">
                <span class="material-symbols-outlined text-[20px]">e911_emergency</span>
                <span>Quirófanos</span>
              </div>
              <p class="font-body-sm text-body-sm text-on-surface-variant">
                Quirófano equipado para cirugía general y laparoscópica mínimamente invasiva, con soporte de anestesiología y flujo laminar.
              </p>
            </div>
            <div class="pt-2 text-label-sm font-label-sm text-primary flex items-center gap-1 font-semibold">
              <span>Cirugía Abierta y Laparoscopía</span>
            </div>
          </div>
        </div>

        <!-- 6. Hospitalización -->
        <div class="bg-surface rounded-2xl overflow-hidden shadow-sm flex flex-col group border border-outline-variant">
          <div class="relative h-48 overflow-hidden bg-surface-container-low">
            <img
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
              alt="Habitaciones de hospitalización y recuperación segura en Hospital San Marcos"
              src="img\Hospitalizacion.png"
            />
            <div class="absolute top-3 left-3 bg-inverse-surface/80 text-surface text-label-sm font-label-sm px-2.5 py-1 rounded-full backdrop-blur-sm">
              Descanso y Cuidado
            </div>
          </div>
          <div class="p-space-lg flex-1 flex flex-col justify-between space-y-space-sm">
            <div>
              <div class="flex items-center gap-2 text-primary font-bold text-title-md font-title-md mb-1">
                <span class="material-symbols-outlined text-[20px]">bed</span>
                <span>Hospitalización</span>
              </div>
              <p class="font-body-sm text-body-sm text-on-surface-variant">
                Habitaciones cómodas para recuperación y monitoreo seguro con asistencia de enfermería continua y privacidad familiar.
              </p>
            </div>
            <div class="pt-2 text-label-sm font-label-sm text-primary flex items-center gap-1 font-semibold">
              <span>Monitoreo continuo y calidez</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- CIERRE Y LLAMADA A LA ACCIÓN -->
  <section class="w-full bg-gradient-to-r from-inverse-surface via-primary to-inverse-surface text-surface py-space-2xl md:py-24">
    <div class="max-w-7xl mx-auto px-gutter text-center space-y-space-lg">
      <div class="max-w-3xl mx-auto space-y-space-sm">
        <span class="font-label-sm text-label-sm uppercase tracking-widest text-primary-fixed font-bold block">
          TU SALUD EN MANOS DE EXPERTOS
        </span>
        <h2 class="font-headline-lg text-headline-md md:text-display-mobile text-surface">
          Estamos comprometidos con tu bienestar y el de tu familia
        </h2>
        <p class="font-body-lg text-body-lg text-surface-container-highest max-w-2xl mx-auto leading-relaxed">
          Descubre nuestra cartera de especialidades médicas o agenda una consulta con nuestros profesionales hoy mismo en Pasaje, El Oro.
        </p>
      </div>
      <div class="pt-space-sm flex flex-col sm:flex-row items-center justify-center gap-space-md">
        <a
          class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-primary-container text-on-primary font-title-md text-label-md font-semibold px-space-xl py-3.5 rounded-full hover:bg-inverse-primary hover:text-on-primary-container transition-all shadow-md"
          href="<?= e(url('/servicios')) ?>"
        >
          <span>Conocer nuestros servicios médicos</span>
          <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
        </a>
        <a
          class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-surface-container-lowest text-on-surface font-title-md text-label-md font-semibold px-space-xl py-3.5 rounded-full hover:bg-surface-container-high transition-all shadow-sm"
          href="<?= e(url('/agendar-cita')) ?>"
        >
          <span class="material-symbols-outlined text-[18px]">calendar_month</span>
          <span>Agendar cita</span>
        </a>
      </div>
    </div>
  </section>
</div>
