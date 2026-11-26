/**
 * Módulo Interactivo: Página Especialidades
 * - Buscador en tiempo real insensible a mayúsculas/minúsculas y tildes
 * - 6 filtros por categoría
 * - Contador automático de resultados y estado vacío
 */

import { normalizeText } from '../utils/normalize.js';

export function initEspecialidadesPage() {
  const searchInput = document.getElementById('specialty-search-input');
  const clearBtn = document.getElementById('specialty-search-clear');
  const resultsCountEl = document.getElementById('specialty-results-count');
  const emptyStateEl = document.getElementById('specialty-empty-state');
  const resetBtn = document.getElementById('specialty-reset-btn');
  const gridEl = document.getElementById('specialty-grid');
  const categoryButtons = Array.from(document.querySelectorAll('[data-specialty-cat]'));
  const cards = Array.from(document.querySelectorAll('[data-specialty-card="true"]'));

  if (!searchInput || !gridEl || cards.length === 0) {
    return;
  }

  let activeCategory = 'all';

  const activeBtnClasses = ['bg-primary', 'text-on-primary', 'font-bold'];
  const inactiveBtnClasses = [
    'bg-surface-container-low',
    'text-on-surface-variant',
    'hover:bg-surface-container',
    'hover:text-on-surface',
  ];

  function updateCategoryButtonsUI() {
    categoryButtons.forEach((btn) => {
      const catId = btn.getAttribute('data-specialty-cat') || 'all';
      const isActive = catId === activeCategory;
      btn.setAttribute('aria-selected', isActive ? 'true' : 'false');

      if (isActive) {
        btn.classList.remove(...inactiveBtnClasses);
        btn.classList.add(...activeBtnClasses);
      } else {
        btn.classList.remove(...activeBtnClasses);
        btn.classList.add(...inactiveBtnClasses);
      }
    });
  }

  function applyFilters() {
    const rawQuery = searchInput.value || '';
    const normalizedQuery = normalizeText(rawQuery);

    if (clearBtn) {
      if (rawQuery.length > 0) {
        clearBtn.classList.remove('hidden');
      } else {
        clearBtn.classList.add('hidden');
      }
    }

    let visibleCount = 0;

    cards.forEach((card) => {
      const cardCategories = card.getAttribute('data-category') || '';
      const cardName = normalizeText(card.getAttribute('data-name') || '');
      const cardDesc = normalizeText(card.getAttribute('data-description') || '');

      const matchCategory =
        activeCategory === 'all' || cardCategories.includes(activeCategory);

      const matchQuery =
        !normalizedQuery ||
        cardName.includes(normalizedQuery) ||
        cardDesc.includes(normalizedQuery);

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
          ? 'Mostrando 1 especialidad'
          : `Mostrando ${visibleCount} especialidades`;
    }

    if (emptyStateEl) {
      if (visibleCount === 0) {
        emptyStateEl.classList.remove('hidden');
        gridEl.classList.add('hidden');
      } else {
        emptyStateEl.classList.add('hidden');
        gridEl.classList.remove('hidden');
      }
    }
  }

  // Eventos de búsqueda en tiempo real
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
      activeCategory = btn.getAttribute('data-specialty-cat') || 'all';
      updateCategoryButtonsUI();
      applyFilters();
    });
  });

  // Botón restablecer en estado vacío
  if (resetBtn) {
    resetBtn.addEventListener('click', () => {
      searchInput.value = '';
      activeCategory = 'all';
      updateCategoryButtonsUI();
      applyFilters();
    });
  }

  // Soporte inicial si existe parámetro ?search= en la URL (validado en longitud)
  const urlParams = new URLSearchParams(window.location.search);
  const initialSearch = urlParams.get('search');
  if (initialSearch) {
    searchInput.value = initialSearch.trim().slice(0, 80);
  }

  updateCategoryButtonsUI();
  applyFilters();
}
