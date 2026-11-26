/**
 * Módulo Interactivo: Página Servicios
 * - 4 pestañas de Fichas Operativas y Preparación al Paciente
 * - Desplazamiento suave (smooth scroll) desde las tarjetas de servicios
 * - Acordeón interactivo de las 5 Preguntas Frecuentes (FAQs)
 */

export function initServiciosPage() {
  const detailTabButtons = Array.from(document.querySelectorAll('[data-detail-tab]'));
  const detailPanels = Array.from(document.querySelectorAll('[data-detail-panel]'));
  const scrollDetailTriggers = Array.from(document.querySelectorAll('[data-scroll-detail]'));
  const interactiveSection = document.getElementById('interactive-detail-section');

  const faqToggleButtons = Array.from(document.querySelectorAll('[data-faq-toggle]'));

  // 1. Pestañas de Ficha Operativa
  const activeTabClasses = ['bg-primary', 'text-on-primary', 'shadow-sm'];
  const inactiveTabClasses = [
    'bg-surface-container-low',
    'text-on-surface-variant',
    'hover:bg-surface-container',
  ];

  function selectDetailTab(tabKey) {
    detailTabButtons.forEach((btn) => {
      const key = btn.getAttribute('data-detail-tab');
      const isSelected = key === tabKey;
      btn.setAttribute('aria-selected', isSelected ? 'true' : 'false');

      if (isSelected) {
        btn.classList.remove(...inactiveTabClasses);
        btn.classList.add(...activeTabClasses);
      } else {
        btn.classList.remove(...activeTabClasses);
        btn.classList.add(...inactiveTabClasses);
      }
    });

    detailPanels.forEach((panel) => {
      const key = panel.getAttribute('data-detail-panel');
      if (key === tabKey) {
        panel.classList.remove('hidden');
      } else {
        panel.classList.add('hidden');
      }
    });
  }

  if (detailTabButtons.length > 0 && detailPanels.length > 0) {
    detailTabButtons.forEach((btn) => {
      btn.addEventListener('click', () => {
        const targetKey = btn.getAttribute('data-detail-tab') || 'laboratorio';
        selectDetailTab(targetKey);
      });
    });

    // Botones "Más información" en las tarjetas del catálogo de servicios
    scrollDetailTriggers.forEach((trigger) => {
      trigger.addEventListener('click', () => {
        const rawKey = trigger.getAttribute('data-scroll-detail') || 'laboratorio';
        let resolvedKey = 'laboratorio';

        // Resolver pestaña operativa correspondiente según el servicio seleccionado
        if (rawKey === 'emergencias' || rawKey === 'consulta') {
          resolvedKey = 'laboratorio';
        } else if (rawKey === 'ginecologia') {
          resolvedKey = 'cirugia';
        } else if (['laboratorio', 'imagen', 'cirugia', 'hospitalizacion'].includes(rawKey)) {
          resolvedKey = rawKey;
        }

        selectDetailTab(resolvedKey);

        if (interactiveSection) {
          interactiveSection.scrollIntoView({ behavior: 'smooth' });
        }
      });
    });
  }

  // 2. Acordeón de Preguntas Frecuentes (FAQs)
  if (faqToggleButtons.length > 0) {
    let expandedFaqId = 'emergencias-24h';

    function updateFaqAccordionUI() {
      faqToggleButtons.forEach((btn) => {
        const faqId = btn.getAttribute('data-faq-toggle');
        const isOpen = faqId === expandedFaqId;
        const answerEl = document.querySelector(`[data-faq-answer="${faqId}"]`);
        const iconEl = document.querySelector(`[data-faq-icon="${faqId}"]`);

        btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');

        if (answerEl) {
          if (isOpen) {
            answerEl.classList.remove('hidden');
          } else {
            answerEl.classList.add('hidden');
          }
        }

        if (iconEl) {
          if (isOpen) {
            iconEl.classList.add('rotate-180');
          } else {
            iconEl.classList.remove('rotate-180');
          }
        }
      });
    }

    faqToggleButtons.forEach((btn) => {
      btn.addEventListener('click', () => {
        const faqId = btn.getAttribute('data-faq-toggle');
        expandedFaqId = expandedFaqId === faqId ? null : faqId;
        updateFaqAccordionUI();
      });
    });
  }
}
