<?php
declare(strict_types=1);

if (!defined('HSM_APP')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}
?>
<div class="flex flex-col w-full">
<!-- 1. HERO PRINCIPAL -->
  <section class="relative overflow-hidden bg-gradient-to-b from-surface-container-low via-surface to-surface-bright py-space-xl lg:py-space-2xl">
    <div class="max-w-7xl mx-auto px-gutter relative z-10">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-space-xl items-center">

        <!-- Mensaje y acciones -->
        <div class="lg:col-span-5 flex flex-col space-y-space-lg order-2 lg:order-1">
          <div class="space-y-space-md">
            <h1 class="font-headline-lg text-headline-lg text-on-surface leading-tight tracking-tight lg:text-display lg:leading-none">
              Atención médica integral reunida en <span class="italic font-display text-primary">Pasaje</span>.
            </h1>
            <p class="font-body-lg text-body-lg text-on-surface-variant max-w-xl leading-relaxed">
              Desde 2007 acompañamos a las familias del cantón con 17 especialidades, laboratorio, imagenología, cirugía y emergencia las 24 horas, cuidamos la salud de tu familia sin salir del cantón.
            </p>
          </div>

          <!-- CTAs -->
          <div class="flex flex-wrap items-center gap-space-md pt-space-xs">
            <a
              class="inline-flex items-center justify-center gap-2 bg-primary-container text-on-primary font-title-md text-title-md px-8 py-3.5 rounded-full hover:bg-primary transition-all duration-300 shadow-sm hover:shadow-md transform hover:-translate-y-0.5"
              href="<?= e(url('/agendar-cita')) ?>"
            >
              <span>Agendar una cita</span>
              <span class="material-symbols-outlined text-[20px]" aria-hidden="true">calendar_today</span>
            </a>
            <a
              class="inline-flex items-center justify-center gap-2 bg-surface-container-lowest text-primary font-title-md text-title-md px-7 py-3.5 rounded-full hover:bg-surface-container-high transition-colors shadow-sm"
              href="<?= e(url('/servicios')) ?>"
            >
              <span>Conocer nuestros servicios</span>
              <span class="material-symbols-outlined text-[20px]" aria-hidden="true">arrow_forward</span>
            </a>
          </div>

          <!-- Sellos de confianza -->
          <div class="pt-space-md grid grid-cols-3 gap-space-sm sm:gap-space-md">
            <div class="p-space-sm rounded-xl bg-surface-container-lowest/80 backdrop-blur-sm shadow-sm border border-outline-variant">
              <p class="font-headline-sm text-title-lg text-primary font-bold leading-none">17+</p>
              <p class="font-label-sm text-label-sm text-on-surface-variant mt-1">Años en Pasaje</p>
            </div>
            <div class="p-space-sm rounded-xl bg-surface-container-lowest/80 backdrop-blur-sm shadow-sm border border-outline-variant">
              <p class="font-headline-sm text-title-lg text-secondary font-bold leading-none">100%</p>
              <p class="font-label-sm text-label-sm text-on-surface-variant mt-1">Cuidado local</p>
            </div>
            <div class="p-space-sm rounded-xl bg-surface-container-lowest/80 backdrop-blur-sm shadow-sm border border-outline-variant">
              <p class="font-headline-sm text-title-lg text-tertiary font-bold leading-none">24/7</p>
              <p class="font-label-sm text-label-sm text-on-surface-variant mt-1">Guardia activa</p>
            </div>
          </div>
        </div>

        <!-- Imagen institucional: marco 16:9 (misma proporción que la foto, sin recortes) -->
        <div class="lg:col-span-7 relative order-1 lg:order-2 pb-6 sm:pb-0">
          <div class="relative">
            <div class="absolute -inset-4 bg-gradient-to-tr from-primary-container/20 to-secondary-container/30 rounded-3xl blur-2xl -z-10"></div>

            <div class="relative rounded-2xl overflow-hidden shadow-xl bg-surface-container-lowest">
              <img
                class="w-full object-cover"
                style="display:block;width:100%;height:auto;aspect-ratio:16/9;"
                alt="Fachada de Hospital San Marcos en Pasaje, con ingreso a emergencias 24 horas, laboratorio, tomografía y ecografía"
                src="img/Inicio.png"
              />
            </div>

            <!-- Badge flotante -->
            <div class="absolute -bottom-6 right-3 sm:-right-4 bg-surface-container-lowest p-space-md rounded-2xl shadow-lg flex items-center gap-space-sm max-w-xs border border-outline-variant">
              <div class="w-10 h-10 rounded-full bg-secondary-container text-on-secondary-container flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">verified</span>
              </div>
              <div class="min-w-0">
                <p class="font-label-sm text-label-sm text-secondary font-bold leading-none">Especialistas Médicos</p>
                <p class="font-body-sm text-body-sm text-on-surface-variant truncate mt-0.5">Certificados en El Oro</p>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- 2. TARJETAS DE ACCESOS RÁPIDOS (4 COLUMNAS) -->
  <section class="py-space-xl bg-surface relative z-20">
    <div class="max-w-7xl mx-auto px-gutter">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-space-md">
        <!-- Tarjeta 1: Agendar Cita -->
        <a
          href="<?= e(url('/agendar-cita')) ?>"
          class="group p-space-lg rounded-2xl bg-surface-container-lowest shadow-sm hover:shadow-md transition-all duration-300 transform hover:-translate-y-1 flex flex-col justify-between border border-outline-variant"
        >
          <div>
            <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center mb-space-md group-hover:bg-primary-container group-hover:text-on-primary transition-colors">
              <span class="material-symbols-outlined text-[28px]">event_available</span>
            </div>
            <h2 class="font-title-lg text-title-lg text-on-surface font-semibold group-hover:text-primary transition-colors">
              Agendar Cita
            </h2>
            <p class="font-body-sm text-body-sm text-on-surface-variant mt-2 leading-relaxed">
              Selecciona médico especialista, fecha preferida y horario disponible sin filas.
            </p>
          </div>
          <span class="inline-flex items-center gap-1 font-label-md text-label-md text-primary font-bold mt-space-md">
            <span>Iniciar reserva</span>
            <span class="material-symbols-outlined text-[16px] group-hover:translate-x-1 transition-transform">
              arrow_forward
            </span>
          </span>
        </a>

        <!-- Tarjeta 2: Buscar Especialista -->
        <a
          href="<?= e(url('/especialidades')) ?>"
          class="group p-space-lg rounded-2xl bg-surface-container-lowest shadow-sm hover:shadow-md transition-all duration-300 transform hover:-translate-y-1 flex flex-col justify-between border border-outline-variant"
        >
          <div>
            <div class="w-12 h-12 rounded-xl bg-secondary-container/40 text-secondary flex items-center justify-center mb-space-md group-hover:bg-secondary group-hover:text-on-secondary transition-colors">
              <span class="material-symbols-outlined text-[28px]">stethoscope</span>
            </div>
            <h2 class="font-title-lg text-title-lg text-on-surface font-semibold group-hover:text-secondary transition-colors">
              Buscar Especialista
            </h2>
            <p class="font-body-sm text-body-sm text-on-surface-variant mt-2 leading-relaxed">
              Explora nuestro cuerpo médico por especialidad, perfil clínico y consultorio.
            </p>
          </div>
          <span class="inline-flex items-center gap-1 font-label-md text-label-md text-secondary font-bold mt-space-md">
            <span>Ver directorio</span>
            <span class="material-symbols-outlined text-[16px] group-hover:translate-x-1 transition-transform">
              arrow_forward
            </span>
          </span>
        </a>

        <!-- Tarjeta 3: Laboratorio Clínico -->
        <a
          href="<?= e(url('/servicios')) ?>"
          class="group p-space-lg rounded-2xl bg-surface-container-lowest shadow-sm hover:shadow-md transition-all duration-300 transform hover:-translate-y-1 flex flex-col justify-between border border-outline-variant"
        >
          <div>
            <div class="w-12 h-12 rounded-xl bg-primary-container/20 text-on-primary-container flex items-center justify-center mb-space-md group-hover:bg-primary-container group-hover:text-on-primary transition-colors">
              <span class="material-symbols-outlined text-[28px]">science</span>
            </div>
            <h2 class="font-title-lg text-title-lg text-on-surface font-semibold group-hover:text-primary transition-colors">
              Laboratorio Clínico
            </h2>
            <p class="font-body-sm text-body-sm text-on-surface-variant mt-2 leading-relaxed">
              Pruebas de rutina y análisis de alta precisión con entrega rápida y digital.
            </p>
          </div>
          <span class="inline-flex items-center gap-1 font-label-md text-label-md text-primary font-bold mt-space-md">
            <span>Conocer pruebas</span>
            <span class="material-symbols-outlined text-[16px] group-hover:translate-x-1 transition-transform">
              arrow_forward
            </span>
          </span>
        </a>

        <!-- Tarjeta 4: Emergencias & Ambulancia 24/7 -->
        <a
          href="tel:0987973511"
          class="group p-space-lg rounded-2xl bg-error-container/30 shadow-sm hover:shadow-md transition-all duration-300 transform hover:-translate-y-1 flex flex-col justify-between border border-error-container"
        >
          <div>
            <div class="w-12 h-12 rounded-xl bg-tertiary text-on-tertiary flex items-center justify-center mb-space-md group-hover:scale-105 transition-transform">
              <span class="material-symbols-outlined text-[28px] animate-pulse">emergency</span>
            </div>
            <div class="flex items-center gap-2">
              <h2 class="font-title-lg text-title-lg text-on-error-container font-semibold">
                Emergencias 24/7
              </h2>
            </div>
            <p class="font-body-sm text-body-sm text-on-surface-variant mt-2 leading-relaxed">
              Guardia permanente con soporte para traslado y estabilización inmediata.
            </p>
          </div>
          <span class="inline-flex items-center gap-1 font-label-md text-label-md text-tertiary font-bold mt-space-md">
            <span>Llamar ahora: 098 797 3511</span>
            <span class="material-symbols-outlined text-[16px]">call</span>
          </span>
        </a>
      </div>
    </div>
  </section>

  <!-- 3. BREVE PRESENTACIÓN INSTITUCIONAL ("CONOCE HOSPITAL SAN MARCOS") -->
  <section class="py-space-2xl bg-surface-container-low">
    <div class="max-w-7xl mx-auto px-gutter">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-space-xl items-center">
        <!-- Visual & Metric side -->
        <div class="lg:col-span-5 space-y-space-md">
          <div class="relative rounded-2xl overflow-hidden shadow-md">
            <img
              class="w-full h-80 object-cover"
              alt="Instalaciones hospitalarias de Hospital San Marcos en Pasaje"
              src="img/Inicio2.png"
            />
            <div class="absolute bottom-4 left-4 right-4 p-space-md bg-surface-container-lowest/90 backdrop-blur-md rounded-xl shadow-sm">
              <div class="flex items-center justify-between">
                <div>
                  <span class="font-label-sm text-label-sm text-outline uppercase tracking-wider block">
                    Localización
                  </span>
                  <span class="font-title-md text-title-md text-on-surface font-semibold">
                    Av. Quito y Sucre, Pasaje
                  </span>
                </div>
                <div class="w-10 h-10 rounded-full bg-primary/10 text-primary flex items-center justify-center">
                  <span class="material-symbols-outlined text-[20px]">pin_drop</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Copy side -->
        <div class="lg:col-span-7 flex flex-col space-y-space-md">
          <h2 class="font-headline-md text-headline-md text-on-surface">
            Diecisiete años cultivando un servicio de salud cálido, digno y eficiente en Pasaje.
          </h2>
          <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
            Nacimos con la convicción de que las familias pasajeñas no debían trasladarse fuera del cantón para recibir diagnósticos precisos y cuidados hospitalarios de alta calidad. Hoy reunimos a profesionales comprometidos con la vida y la recuperación integral de cada paciente.
          </p>

          <!-- Metrics summary grid -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-space-md pt-space-xs">
            <div class="p-space-md bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant">
              <span class="material-symbols-outlined text-primary text-[28px]">history_toggle_off</span>
              <p class="font-headline-sm text-headline-sm text-on-surface mt-2 font-bold">+17 Años</p>
              <p class="font-body-sm text-body-sm text-on-surface-variant">Sirviendo a Pasaje</p>
            </div>
            <div class="p-space-md bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant">
              <span class="material-symbols-outlined text-secondary text-[28px]">groups</span>
              <p class="font-headline-sm text-headline-sm text-on-surface mt-2 font-bold">Multidisciplinar</p>
              <p class="font-body-sm text-body-sm text-on-surface-variant">Equipo articulado</p>
            </div>
            <div class="p-space-md bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant">
              <span class="material-symbols-outlined text-primary-container text-[28px]">apartment</span>
              <p class="font-headline-sm text-headline-sm text-on-surface mt-2 font-bold">Moderna</p>
              <p class="font-body-sm text-body-sm text-on-surface-variant">Infraestructura clínica</p>
            </div>
          </div>

          <div class="pt-space-sm">
            <a
              class="inline-flex items-center gap-2 text-primary font-title-md text-title-md hover:underline font-semibold"
              href="<?= e(url('/nosotros')) ?>"
            >
              <span>Conocer nuestra historia y valores</span>
              <span class="material-symbols-outlined text-[18px]">east</span>
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- 4. SERVICIOS DESTACADOS (4 RESÚMENES) -->
  <section class="py-space-2xl bg-surface">
    <div class="max-w-7xl mx-auto px-gutter space-y-space-xl">
      <div class="flex flex-col md:flex-row md:items-end justify-between gap-space-md">
        <div>
          <span class="font-label-sm text-label-sm uppercase tracking-wider text-primary font-bold block mb-1">
            Capacidad Resolutiva
          </span>
          <h2 class="font-headline-md text-headline-md text-on-surface">
            Servicios Clínicos Principales
          </h2>
        </div>
        <a
          class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-surface-container text-primary font-title-md text-label-md hover:bg-surface-container-high transition-colors"
          href="<?= e(url('/servicios')) ?>"
        >
          <span>Ver todos los servicios</span>
          <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
        </a>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-space-lg">
        <!-- Servicio 1: Emergencias -->
        <div class="p-space-lg rounded-2xl bg-surface-container-lowest shadow-sm hover:shadow-md transition-all flex flex-col justify-between border border-outline-variant">
          <div>
            <div class="w-12 h-12 rounded-xl bg-error-container text-on-error-container flex items-center justify-center mb-space-md">
              <span class="material-symbols-outlined text-[26px]">medical_services</span>
            </div>
            <h3 class="font-title-lg text-title-lg text-on-surface font-semibold">
              Emergencias y Urgencias 24h
            </h3>
            <p class="font-body-sm text-body-sm text-on-surface-variant mt-2 leading-relaxed">
              Triage inmediato, sala de observación y personal calificado disponible las 24 horas del día.
            </p>
          </div>
          <div class="mt-space-md pt-space-md border-t border-surface-container">
            <span class="font-label-sm text-label-sm text-tertiary font-bold uppercase tracking-wider">
              Guardia activa continua
            </span>
          </div>
        </div>

        <!-- Servicio 2: Consulta Externa -->
        <div class="p-space-lg rounded-2xl bg-surface-container-lowest shadow-sm hover:shadow-md transition-all flex flex-col justify-between border border-outline-variant">
          <div>
            <div class="w-12 h-12 rounded-xl bg-surface-container-high text-primary flex items-center justify-center mb-space-md">
              <span class="material-symbols-outlined text-[26px]">clinical_notes</span>
            </div>
            <h3 class="font-title-lg text-title-lg text-on-surface font-semibold">
              Consulta Externa
            </h3>
            <p class="font-body-sm text-body-sm text-on-surface-variant mt-2 leading-relaxed">
              Evaluación especializada y seguimiento programado para pacientes pediátricos y adultos.
            </p>
          </div>
          <div class="mt-space-md pt-space-md border-t border-surface-container">
            <span class="font-label-sm text-label-sm text-primary font-bold uppercase tracking-wider">
              Citas programadas
            </span>
          </div>
        </div>

        <!-- Servicio 3: Cirugía General -->
        <div class="p-space-lg rounded-2xl bg-surface-container-lowest shadow-sm hover:shadow-md transition-all flex flex-col justify-between border border-outline-variant">
          <div>
            <div class="w-12 h-12 rounded-xl bg-secondary-container/40 text-secondary flex items-center justify-center mb-space-md">
              <span class="material-symbols-outlined text-[26px]">vital_signs</span>
            </div>
            <h3 class="font-title-lg text-title-lg text-on-surface font-semibold">
              Cirugía y Laparoscopía
            </h3>
            <p class="font-body-sm text-body-sm text-on-surface-variant mt-2 leading-relaxed">
              Quirófano equipado para intervenciones quirúrgicas de mínima invasión y recuperación rápida.
            </p>
          </div>
          <div class="mt-space-md pt-space-md border-t border-surface-container">
            <span class="font-label-sm text-label-sm text-secondary font-bold uppercase tracking-wider">
              Procedimientos seguros
            </span>
          </div>
        </div>

        <!-- Servicio 4: Laboratorio e Imagenología -->
        <div class="p-space-lg rounded-2xl bg-surface-container-lowest shadow-sm hover:shadow-md transition-all flex flex-col justify-between border border-outline-variant">
          <div>
            <div class="w-12 h-12 rounded-xl bg-primary-container/20 text-on-primary-container flex items-center justify-center mb-space-md">
              <span class="material-symbols-outlined text-[26px]">radiology</span>
            </div>
            <h3 class="font-title-lg text-title-lg text-on-surface font-semibold">
              Laboratorio e Imagen
            </h3>
            <p class="font-body-sm text-body-sm text-on-surface-variant mt-2 leading-relaxed">
              Ecografía, radiología digital y análisis clínicos completos para diagnósticos certeros.
            </p>
          </div>
          <div class="mt-space-md pt-space-md border-t border-surface-container">
            <span class="font-label-sm text-label-sm text-primary font-bold uppercase tracking-wider">
              Resultados precisos
            </span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- 5. ESPECIALIDADES DESTACADAS (4 PREVIEWS) -->
  <section class="py-space-2xl bg-surface-container-low">
    <div class="max-w-7xl mx-auto px-gutter space-y-space-xl">
      <div class="flex flex-col md:flex-row md:items-end justify-between gap-space-md">
        <div>
          <span class="font-label-sm text-label-sm uppercase tracking-wider text-secondary font-bold block mb-1">
            Cuerpo Médico
          </span>
          <h2 class="font-headline-md text-headline-md text-on-surface">
            Especialidades Médicas
          </h2>
        </div>
        <a
          class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-surface-container-lowest text-on-surface font-title-md text-label-md hover:bg-surface-container-high transition-colors shadow-sm"
          href="<?= e(url('/especialidades')) ?>"
        >
          <span>Explorar especialidades</span>
          <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
        </a>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-space-lg">
        <!-- Esp 1: Ginecología -->
        <div class="p-space-lg rounded-2xl bg-surface-container-lowest shadow-sm hover:shadow-md transition-all duration-300 border border-outline-variant">
          <div class="w-12 h-12 rounded-xl bg-surface-container-highest text-primary flex items-center justify-center mb-space-md">
            <span class="material-symbols-outlined text-[28px]">pregnant_woman</span>
          </div>
          <h3 class="font-title-lg text-title-lg text-on-surface font-semibold">
            Ginecología y Obstetricia
          </h3>
          <p class="font-body-sm text-body-sm text-on-surface-variant mt-2 leading-relaxed">
            Control prenatal integral, atención de partos seguros y salud reproductiva para la mujer.
          </p>
          <div class="mt-space-md flex items-center gap-2 text-primary font-label-md text-label-md font-semibold">
            <span>Atención especializada</span>
          </div>
        </div>

        <!-- Esp 2: Medicina Interna -->
        <div class="p-space-lg rounded-2xl bg-surface-container-lowest shadow-sm hover:shadow-md transition-all duration-300 border border-outline-variant">
          <div class="w-12 h-12 rounded-xl bg-secondary-container/40 text-secondary flex items-center justify-center mb-space-md">
            <span class="material-symbols-outlined text-[28px]">stethoscope</span>
          </div>
          <h3 class="font-title-lg text-title-lg text-on-surface font-semibold">
            Medicina Interna
          </h3>
          <p class="font-body-sm text-body-sm text-on-surface-variant mt-2 leading-relaxed">
            Diagnóstico clínico preventivo, manejo de hipertensión, diabetes y cuadros sistémicos.
          </p>
          <div class="mt-space-md flex items-center gap-2 text-secondary font-label-md text-label-md font-semibold">
            <span>Control metabólico</span>
          </div>
        </div>

        <!-- Esp 3: Traumatología -->
        <div class="p-space-lg rounded-2xl bg-surface-container-lowest shadow-sm hover:shadow-md transition-all duration-300 border border-outline-variant">
          <div class="w-12 h-12 rounded-xl bg-primary-container/20 text-on-primary-container flex items-center justify-center mb-space-md">
            <span class="material-symbols-outlined text-[28px]">orthopedics</span>
          </div>
          <h3 class="font-title-lg text-title-lg text-on-surface font-semibold">
            Traumatología y Ortopedia
          </h3>
          <p class="font-body-sm text-body-sm text-on-surface-variant mt-2 leading-relaxed">
            Tratamiento de fracturas, afecciones articulares, lesiones musculares y corrección ortopédica.
          </p>
          <div class="mt-space-md flex items-center gap-2 text-primary font-label-md text-label-md font-semibold">
            <span>Recuperación motriz</span>
          </div>
        </div>

        <!-- Esp 4: Cardiología -->
        <div class="p-space-lg rounded-2xl bg-surface-container-lowest shadow-sm hover:shadow-md transition-all duration-300 border border-outline-variant">
          <div class="w-12 h-12 rounded-xl bg-error-container/40 text-tertiary flex items-center justify-center mb-space-md">
            <span class="material-symbols-outlined text-[28px]">favorite</span>
          </div>
          <h3 class="font-title-lg text-title-lg text-on-surface font-semibold">
            Cardiología
          </h3>
          <p class="font-body-sm text-body-sm text-on-surface-variant mt-2 leading-relaxed">
            Evaluación cardiovascular preventiva, electrocardiogramas y seguimiento de arritmias.
          </p>
          <div class="mt-space-md flex items-center gap-2 text-tertiary font-label-md text-label-md font-semibold">
            <span>Cuidado cardiovascular</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- 6. ¿POR QUÉ ELEGIR HOSPITAL SAN MARCOS? (3 PILARES) -->
  <section class="py-space-2xl bg-surface">
    <div class="max-w-7xl mx-auto px-gutter space-y-space-xl">
      <div class="max-w-2xl mx-auto text-center space-y-space-sm">
        <span class="font-label-sm text-label-sm uppercase tracking-wider text-primary font-bold">
          Nuestra Diferencia
        </span>
        <h2 class="font-headline-md text-headline-md text-on-surface">
          ¿Por qué elegir Hospital San Marcos?
        </h2>
        <p class="font-body-md text-body-md text-on-surface-variant">
          Un modelo de atención que antepone el respeto por el paciente, la prontitud médica y la tranquilidad familiar.
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-space-lg">
        <!-- Pilar 1 -->
        <div class="p-space-xl rounded-2xl bg-surface-container-lowest shadow-sm hover:shadow-md transition-all text-center flex flex-col items-center border border-outline-variant">
          <div class="w-16 h-16 rounded-full bg-primary/10 text-primary flex items-center justify-center mb-space-md">
            <span class="material-symbols-outlined text-[32px]">volunteer_activism</span>
          </div>
          <h3 class="font-title-lg text-title-lg text-on-surface font-semibold">
            Atención humana y cercana
          </h3>
          <p class="font-body-md text-body-md text-on-surface-variant mt-3 leading-relaxed">
            Trato digno, empático y personalizado para cada paciente. Escuchamos activamente para acompañar a su familia con respeto.
          </p>
        </div>

        <!-- Pilar 2 -->
        <div class="p-space-xl rounded-2xl bg-surface-container-lowest shadow-sm hover:shadow-md transition-all text-center flex flex-col items-center border border-outline-variant">
          <div class="w-16 h-16 rounded-full bg-secondary-container/50 text-secondary flex items-center justify-center mb-space-md">
            <span class="material-symbols-outlined text-[32px]">hub</span>
          </div>
          <h3 class="font-title-lg text-title-lg text-on-surface font-semibold">
            Todo en un solo lugar
          </h3>
          <p class="font-body-md text-body-md text-on-surface-variant mt-3 leading-relaxed">
            Consultas, diagnóstico, laboratorio, quirófanos y hospitalización centralizados en Pasaje para evitar traslados innecesarios.
          </p>
        </div>

        <!-- Pilar 3 -->
        <div class="p-space-xl rounded-2xl bg-surface-container-lowest shadow-sm hover:shadow-md transition-all text-center flex flex-col items-center border border-outline-variant">
          <div class="w-16 h-16 rounded-full bg-surface-container-highest text-on-primary-container flex items-center justify-center mb-space-md">
            <span class="material-symbols-outlined text-[32px]">workspace_premium</span>
          </div>
          <h3 class="font-title-lg text-title-lg text-on-surface font-semibold">
            Trayectoria comunitaria desde 2007
          </h3>
          <p class="font-body-md text-body-md text-on-surface-variant mt-3 leading-relaxed">
            Compromiso sostenido con la salud y bienestar de El Oro. Casi dos décadas de respaldo clínico y vocación de servicio.
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- 7. LLAMADO A LA ACCIÓN (BANNER CTA FINAL) -->
  <section class="py-space-2xl bg-surface">
    <div class="max-w-7xl mx-auto px-gutter">
      <div class="p-space-xl lg:p-space-2xl rounded-3xl bg-surface-container-low shadow-sm flex flex-col lg:flex-row items-center justify-between gap-space-lg relative overflow-hidden border border-outline-variant">
        <div class="absolute -right-20 -bottom-20 w-80 h-80 rounded-full bg-primary-container/10 blur-3xl pointer-events-none"></div>
        <div class="max-w-xl space-y-space-sm text-center lg:text-left relative z-10">
          <span class="font-label-sm text-label-sm uppercase tracking-wider text-primary font-bold">
            Respuesta Oportuna
          </span>
          <h2 class="font-headline-lg text-headline-lg text-on-surface leading-snug">
            ¿Necesitas atención médica oportuna?
          </h2>
          <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
            Programa tu consulta médica o ponte en contacto con nuestro equipo de admisión. Estamos en el corazón de Pasaje para asistirte.
          </p>
        </div>
        <div class="flex flex-col sm:flex-row items-center gap-space-md w-full lg:w-auto shrink-0 relative z-10">
          <a
            href="<?= e(url('/agendar-cita')) ?>"
            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-primary-container text-on-primary font-title-md text-title-md px-8 py-3.5 rounded-full hover:bg-primary transition-all shadow-sm hover:shadow"
          >
            <span>Solicitar cita médica</span>
            <span class="material-symbols-outlined text-[20px]">calendar_today</span>
          </a>
          <a
            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-surface-container-lowest text-primary font-title-md text-title-md px-7 py-3.5 rounded-full hover:bg-surface-container-high transition-colors shadow-sm"
            href="tel:0987973511"
          >
            <span class="material-symbols-outlined text-[20px]">call</span>
            <span>Recepción (098 797 3511)</span>
          </a>
        </div>
      </div>
    </div>
  </section>
</div>
