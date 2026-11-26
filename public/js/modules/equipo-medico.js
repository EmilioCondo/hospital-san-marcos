/**
 * Módulo Interactivo: Página Equipo Médico
 * - Buscador en tiempo real por nombre o especialidad (insensible a mayúsculas y tildes)
 * - 6 pestañas de categorías con combinación de filtros
 * - Lectura de parámetro ?search= en la URL
 * - Modal de perfil médico (apertura, cierre por botón, tecla Escape y clic fuera)
 */

import { normalizeText } from '../utils/normalize.js';

export function initEquipoMedicoPage() {
  const searchInput = document.getElementById('doctorSearchInput');
  const clearBtn = document.getElementById('doctor-search-clear');
  const resultsCountEl = document.getElementById('doctor-results-count');
  const emptyStateEl = document.getElementById('doctor-empty-state');
  const resetBtn = document.getElementById('doctor-reset-btn');
  const gridEl = document.getElementById('doctor-grid');
  const categoryButtons = Array.from(document.querySelectorAll('[data-doctor-cat]'));
  const cards = Array.from(document.querySelectorAll('[data-doctor-card="true"]'));

  // Elementos del Modal de Perfil Médico
  const modalEl = document.getElementById('doctor-profile-modal');
  const modalInitialsEl = document.getElementById('modal-doctor-initials');
  const modalBadgeEl = document.getElementById('modal-doctor-badge');
  const modalNameEl = document.getElementById('modal-doctor-name');
  const modalSpecialtyEl = document.getElementById('modal-doctor-specialty');
  const modalDescriptionEl = document.getElementById('modal-doctor-description');
  const modalAppointmentLinkEl = document.getElementById('modal-doctor-appointment-link');
  const closeModalButtons = Array.from(document.querySelectorAll('[data-close-doctor-modal]'));

  if (!searchInput || !gridEl || cards.length === 0) {
    return;
  }

  let selectedCategory = 'all';

  const activeTabClasses = ['bg-primary', 'text-on-primary'];
  const inactiveTabClasses = [
    'bg-surface-container',
    'text-on-surface',
    'hover:bg-surface-container-high',
  ];

  // Mapa de equivalencias clínicas para que los enlaces ?search= desde Especialidades
  // (ej. "Ginecología y obstetricia", "Cirugía general", "Cardiología") coincidan también con los títulos del médico ("Ginecólogo obstetra", "Cirujano general", "Cardiólogo")
  const specialtyEquivalences = {
    'ginecologia y obstetricia': 'ginecologo obstetra',
    'cirugia general': 'cirujano general',
    'cirugia pediatrica': 'cirujano pediatra',
    'cirugia estetico-maxilofacial': 'cirujano plastico maxilofacial',
    'neurocirugia': 'neurocirujano',
    'cardiologia': 'cardiologo',
    'gastroenterologia': 'gastroenterologo',
    'traumatologia': 'traumatologo',
    'urologia': 'urologo',
    'otorrinolaringologia': 'otorrinolaringologo',
    'psicologia': 'psicologo',
    'diabetologia': 'diabetologo',
  };

  function updateCategoryTabsUI() {
    categoryButtons.forEach((btn) => {
      const catId = btn.getAttribute('data-doctor-cat') || 'all';
      const isActive = catId === selectedCategory;
      btn.setAttribute('aria-selected', isActive ? 'true' : 'false');

      if (isActive) {
        btn.classList.remove(...inactiveTabClasses);
        btn.classList.add(...activeTabClasses);
      } else {
        btn.classList.remove(...activeTabClasses);
        btn.classList.add(...inactiveTabClasses);
      }
    });
  }

  function applyFilters() {
    const rawQuery = searchInput.value || '';
    const normalizedQuery = normalizeText(rawQuery);
    const mappedEquivalent = specialtyEquivalences[normalizedQuery] || '';

    if (clearBtn) {
      if (rawQuery.length > 0) {
        clearBtn.classList.remove('hidden');
      } else {
        clearBtn.classList.add('hidden');
      }
    }

    let visibleCount = 0;

    cards.forEach((card) => {
      const cardCat = card.getAttribute('data-category') || '';
      const cardName = normalizeText(card.getAttribute('data-name') || '');
      const cardSpec = normalizeText(card.getAttribute('data-specialty') || '');
      const cardBadge = normalizeText(card.getAttribute('data-badge') || '');

      const matchCategory =
        selectedCategory === 'all' || cardCat === selectedCategory;

      const matchQuery =
        !normalizedQuery ||
        cardName.includes(normalizedQuery) ||
        cardSpec.includes(normalizedQuery) ||
        cardBadge.includes(normalizedQuery) ||
        (mappedEquivalent !== '' && cardSpec.includes(mappedEquivalent));

      if (matchCategory && matchQuery) {
        card.classList.remove('hidden');
        visibleCount++;
      } else {
        card.classList.add('hidden');
      }
    });

    if (resultsCountEl) {
      resultsCountEl.textContent =
        visibleCount === 1
          ? 'Mostrando 1 especialista acreditado'
          : `Mostrando ${visibleCount} especialistas acreditados`;
    }

    if (emptyStateEl) {
      if (visibleCount === 0) {
        emptyStateEl.classList.remove('hidden');
        emptyStateEl.classList.add('flex');
        gridEl.classList.add('hidden');
      } else {
        emptyStateEl.classList.add('hidden');
        emptyStateEl.classList.remove('flex');
        gridEl.classList.remove('hidden');
      }
    }
  }

  // Funciones de apertura y cierre del Modal de Perfil Médico
  function openDoctorModal(card) {
    if (!modalEl || !card) return;

    const name = card.getAttribute('data-name') || '';
    const specialty = card.getAttribute('data-specialty') || '';
    const initials = card.getAttribute('data-initials') || '';
    const description = card.getAttribute('data-description') || '';
    const accredited = card.getAttribute('data-accredited') === '1';
    const appointmentUrl = card.getAttribute('data-appointment-url') || '#';

    if (modalInitialsEl) modalInitialsEl.textContent = initials;
    if (modalNameEl) modalNameEl.textContent = name;
    if (modalSpecialtyEl) modalSpecialtyEl.textContent = specialty;
    if (modalDescriptionEl) modalDescriptionEl.textContent = description;
    if (modalBadgeEl) {
      modalBadgeEl.textContent = accredited
        ? 'Profesional Acreditado'
        : 'Disponibilidad por confirmar';
    }
    if (modalAppointmentLinkEl) {
      modalAppointmentLinkEl.setAttribute('href', appointmentUrl);
    }

    modalEl.classList.remove('hidden');
    modalEl.classList.add('flex');
    document.body.style.overflow = 'hidden';
  }

  function closeDoctorModal() {
    if (!modalEl) return;
    modalEl.classList.add('hidden');
    modalEl.classList.remove('flex');
    document.body.style.overflow = '';
  }

  // Eventos de búsqueda
  searchInput.addEventListener('input', applyFilters);

  if (clearBtn) {
    clearBtn.addEventListener('click', () => {
      searchInput.value = '';
      applyFilters();
      searchInput.focus();
    });
  }

  // Eventos de categorías
  categoryButtons.forEach((btn) => {
    btn.addEventListener('click', () => {
      selectedCategory = btn.getAttribute('data-doctor-cat') || 'all';
      updateCategoryTabsUI();
      applyFilters();
    });
  });

  // Botón restablecer filtros
  if (resetBtn) {
    resetBtn.addEventListener('click', () => {
      searchInput.value = '';
      selectedCategory = 'all';
      updateCategoryTabsUI();
      applyFilters();
    });
  }

  // Eventos para abrir modal desde cada tarjeta
  cards.forEach((card) => {
    const openBtn = card.querySelector('[data-open-doctor-modal]');
    if (openBtn) {
      openBtn.addEventListener('click', () => openDoctorModal(card));
    }
  });

  // Eventos para cerrar modal (botones de cierre)
  closeModalButtons.forEach((btn) => {
    btn.addEventListener('click', closeDoctorModal);
  });

  // Cerrar modal al hacer clic fuera del contenedor (backdrop)
  if (modalEl) {
    modalEl.addEventListener('click', (event) => {
      if (event.target === modalEl) {
        closeDoctorModal();
      }
    });
  }

  // Cerrar modal con tecla Escape
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && modalEl && !modalEl.classList.contains('hidden')) {
      closeDoctorModal();
    }
  });

  // Leer ?search= de la URL al iniciar (validado en longitud)
  const urlParams = new URLSearchParams(window.location.search);
  const urlSearch = urlParams.get('search');
  if (urlSearch !== null) {
    const cleanSearch = urlSearch.trim().slice(0, 80);
    if (searchInput.value !== cleanSearch) {
      searchInput.value = cleanSearch;
    }
  }

  updateCategoryTabsUI();
  applyFilters();
}
