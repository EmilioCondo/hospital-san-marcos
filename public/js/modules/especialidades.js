/**
 * Módulo Interactivo: Página Especialidades
 * - Buscador en tiempo real insensible a mayúsculas/minúsculas y tildes
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
  const cards = Array.from(document.querySelectorAll('[data-specialty-card="true"]'));

  if (!searchInput || !gridEl || cards.length === 0) {
    return;
  }

  function applySearch() {
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
      const cardName = normalizeText(card.getAttribute('data-name') || '');
      const cardDesc = normalizeText(card.getAttribute('data-description') || '');

      const matchQuery =
        !normalizedQuery ||
        cardName.includes(normalizedQuery) ||
        cardDesc.includes(normalizedQuery);

      if (matchQuery) {
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

  searchInput.addEventListener('input', applySearch);

  if (clearBtn) {
    clearBtn.addEventListener('click', () => {
      searchInput.value = '';
      applySearch();
      searchInput.focus();
    });
  }

  if (resetBtn) {
    resetBtn.addEventListener('click', () => {
      searchInput.value = '';
      applySearch();
    });
  }

  const urlParams = new URLSearchParams(window.location.search);
  const initialSearch = urlParams.get('search');
  if (initialSearch) {
    searchInput.value = initialSearch.trim().slice(0, 80);
  }

  applySearch();
}
