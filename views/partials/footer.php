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
                <div class="flex flex-col items-start gap-space-sm">
          <a
            href="<?= e(url('/')) ?>"
            style="display: block; width: 100%;"
          >
            <img
              src="<?= e(url('/img/Logo.png')) ?>"
              alt="Hospital San Marcos"
              style="display: block; height: auto; width: 100%; max-width: 280px; object-fit: contain; filter: brightness(0) invert(1);"
              
            >
          </a>
          <span class="font-label-sm text-label-sm text-surface-container-highest tracking-widest uppercase">
            Pasaje • El Oro
          </span>
        </div>
        <p class="font-body-sm text-body-sm text-surface-container-highest leading-relaxed">
          Atención médica humana, confiable y especializada al servicio de las familias de Pasaje y la provincia de El Oro desde 2007.
        </p>
                <div>
          <span class="font-label-sm text-label-sm font-bold text-surface uppercase tracking-widest" style="display: block; margin-bottom: 12px;">
            Síguenos
          </span>

          <div class="hsm-social">
            <a
              class="hsm-social__link hsm-social__link--instagram"
              href="https://instagram.com/hospital_sanmarcosec/"
              target="_blank"
              rel="noopener noreferrer"
              aria-label="Instagram de Hospital San Marcos"
              title="Instagram"
            >
              <i class="fa-brands fa-instagram" aria-hidden="true"></i>
            </a>
            <a
              class="hsm-social__link hsm-social__link--facebook"
              href="https://facebook.com/hospitalsanmarcospasaje/"
              target="_blank"
              rel="noopener noreferrer"
              aria-label="Facebook de Hospital San Marcos"
              title="Facebook"
            >
              <i class="fa-brands fa-facebook-f" aria-hidden="true"></i>
            </a>
            <a
              class="hsm-social__link hsm-social__link--whatsapp"
              href="https://wa.me/593987973511"
              target="_blank"
              rel="noopener noreferrer"
              aria-label="WhatsApp directo con Hospital San Marcos"
              title="WhatsApp"
            >
              <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
            </a>
          </div>
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
