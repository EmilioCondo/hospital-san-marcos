<?php
declare(strict_types=1);

if (!defined('HSM_APP')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}
?>
<footer class="w-full bg-inverse-surface text-surface">
  <div class="max-w-7xl mx-auto px-gutter py-space-2xl">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-space-xl">
      <!-- Col 1: Identity & Brand -->
      <div class="space-y-space-md">
        <div class="flex items-center gap-space-sm">
          <img
            alt="Logo Hospital San Marcos"
            class="h-8 w-auto object-contain brightness-0 invert"
            src="https://lh3.googleusercontent.com/aida/AEtjO1WKRuW43Ca3Xo-ghJmChxSFJW0uop3LTgfO1f1EWp3vzI9jqI7RBOtAy57wNxmJvJxSmd8XXY2v4IKGUBcuje-WfBuXzdWuUtnIC4AGiU3G5PRNAy-SSHTcGOuKSRvULzeC-NSDJJcJH_jhn9xi907Ad6DJSxeQ2NQRhBUGskw6YpCMx1lsJau5g-1P6Pax_dOK5AS9c2LGRGJySKoJQ2C5xKVPAEl7tNq83SM9eNJ9D24-v1Uc3sbwybcG"
          />
          <div class="flex flex-col">
            <span class="font-headline-sm text-title-md font-bold tracking-tight text-surface">
              HOSPITAL SAN MARCOS
            </span>
            <span class="font-label-sm text-label-sm text-surface-container-highest tracking-widest uppercase">
              Pasaje • El Oro
            </span>
          </div>
        </div>
        <p class="font-body-sm text-body-sm text-surface-container-highest leading-relaxed">
          Atención médica humana, confiable y especializada al servicio de las familias de Pasaje y la provincia de El Oro desde 2007.
        </p>
        <div class="flex items-center gap-space-sm">
          <a
            href="https://instagram.com/hospital_sanmarcosec/"
            target="_blank"
            rel="noopener noreferrer"
            class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-surface-container-low/10 text-surface hover:bg-primary transition-colors"
            aria-label="Instagram de Hospital San Marcos"
          >
            <span class="material-symbols-outlined text-[18px]">photo_camera</span>
          </a>
          <a
            href="https://facebook.com/hospitalsanmarcospasaje/"
            target="_blank"
            rel="noopener noreferrer"
            class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-surface-container-low/10 text-surface hover:bg-primary transition-colors"
            aria-label="Facebook de Hospital San Marcos"
          >
            <span class="material-symbols-outlined text-[18px]">public</span>
          </a>
          <a
            class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-surface-container-low/10 text-surface hover:bg-primary transition-colors"
            href="https://wa.me/593987973511"
            rel="noopener noreferrer"
            target="_blank"
            aria-label="WhatsApp directo con Hospital San Marcos"
          >
            <span class="material-symbols-outlined text-[18px]">chat</span>
          </a>
        </div>
      </div>

      <!-- Col 2: Navigation Links -->
      <div class="space-y-space-md">
        <h3 class="font-title-md text-title-md font-semibold text-surface tracking-tight">
          Navegación
        </h3>
        <ul class="space-y-space-sm font-body-sm text-body-sm text-surface-container-highest">
          <li>
            <a class="hover:text-secondary-fixed transition-colors" href="<?= e(url('/')) ?>">
              Inicio
            </a>
          </li>
          <li>
            <a class="hover:text-secondary-fixed transition-colors" href="<?= e(url('/nosotros')) ?>">
              Nosotros
            </a>
          </li>
          <li>
            <a class="hover:text-secondary-fixed transition-colors" href="<?= e(url('/servicios')) ?>">
              Servicios
            </a>
          </li>
          <li>
            <a class="hover:text-secondary-fixed transition-colors" href="<?= e(url('/especialidades')) ?>">
              Especialidades
            </a>
          </li>
          <li>
            <a class="hover:text-secondary-fixed transition-colors" href="<?= e(url('/equipo-medico')) ?>">
              Equipo médico
            </a>
          </li>
          <li>
            <a class="hover:text-secondary-fixed transition-colors" href="<?= e(url('/contacto')) ?>">
              Contacto
            </a>
          </li>
          <li>
            <a class="hover:text-secondary-fixed transition-colors" href="<?= e(url('/agendar-cita')) ?>">
              Agendar cita
            </a>
          </li>
        </ul>
      </div>

      <!-- Col 3: Attention & Headquarter Details -->
      <div class="space-y-space-md">
        <h3 class="font-title-md text-title-md font-semibold text-surface tracking-tight">
          Atención y Sede
        </h3>
        <ul class="space-y-space-sm font-body-sm text-body-sm text-surface-container-highest">
          <li class="flex items-start gap-space-sm">
            <span class="material-symbols-outlined text-[20px] text-primary-container shrink-0 mt-0.5">
              location_on
            </span>
            <span>Av. Quito y Sucre, Pasaje, El Oro, Ecuador</span>
          </li>
          <li class="flex items-center gap-space-sm">
            <span class="material-symbols-outlined text-[20px] text-primary-container shrink-0">
              call
            </span>
            <a class="hover:text-surface transition-colors" href="tel:0987973511">
              Tel: 098 797 3511
            </a>
          </li>
          <li class="flex items-center gap-space-sm">
            <span class="material-symbols-outlined text-[20px] text-secondary-fixed shrink-0">
              chat
            </span>
            <a
              class="hover:text-surface transition-colors"
              href="https://wa.me/593987973511"
              rel="noopener noreferrer"
              target="_blank"
            >
              WhatsApp: +593 98 797 3511
            </a>
          </li>
          <li class="flex items-center gap-space-sm">
            <span class="material-symbols-outlined text-[20px] text-error shrink-0">
              emergency
            </span>
            <span class="font-semibold text-error-container">
              Emergencias las 24 Horas
            </span>
          </li>
        </ul>
      </div>

      <!-- Col 4: Clinical Commitment -->
      <div class="space-y-space-md">
        <h3 class="font-title-md text-title-md font-semibold text-surface tracking-tight">
          Compromiso Clínico
        </h3>
        <p class="font-body-sm text-body-sm text-surface-container-highest leading-relaxed">
          Infraestructura hospitalaria y tecnología médica dispuesta para un cuidado seguro, oportuno y de alta calidad.
        </p>
        <div class="p-space-md rounded-xl bg-surface-container-low/5 space-y-space-xs border border-surface-container-low/10">
          <span class="font-label-sm text-label-sm font-bold text-surface-container-highest uppercase block">
            Centro Hospitalario Registrado
          </span>
          <span class="font-body-sm text-body-sm text-surface-container-highest block">
            Pasaje, Provincia de El Oro
          </span>
        </div>
      </div>
    </div>

    <!-- Bottom Bar: Copyright & Notices -->
    <div class="mt-space-2xl pt-space-lg border-t border-surface-container-low/10 flex flex-col sm:flex-row items-center justify-between gap-space-md font-body-sm text-body-sm text-surface-container-highest">
      <p>© 2026 Hospital San Marcos. Todos los derechos reservados.</p>
      <div class="flex items-center gap-space-lg">
        <a class="hover:text-surface transition-colors" href="<?= e(url('/contacto')) ?>">
          Aviso Legal
        </a>
        <a class="hover:text-surface transition-colors" href="<?= e(url('/contacto')) ?>">
          Política de Privacidad
        </a>
      </div>
    </div>
  </div>
</footer>
