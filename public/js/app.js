/**
 * Punto de entrada principal de JavaScript - Hospital San Marcos
 * Arquitectura modular sin dependencias externas
 */

import { initNavbar } from './modules/navbar.js';
import { initEspecialidadesPage } from './modules/especialidades.js';
import { initEquipoMedicoPage } from './modules/equipo-medico.js';
import { initServiciosPage } from './modules/servicios.js';
import { initAgendarCitaPage } from './modules/agendar-cita.js';
import { initContactoPage } from './modules/contacto.js';

document.addEventListener('DOMContentLoaded', () => {
  initNavbar();
  initEspecialidadesPage();
  initEquipoMedicoPage();
  initServiciosPage();
  initAgendarCitaPage();
  initContactoPage();
});
