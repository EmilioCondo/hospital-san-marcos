/**
 * Módulo Interactivo: Página Servicios
 * - Acordeón interactivo de Preguntas Frecuentes (FAQs)
 */

export function initServiciosPage() {
  const faqToggleButtons = Array.from(document.querySelectorAll('[data-faq-toggle]'));
  if (faqToggleButtons.length === 0) {
    return;
  }

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
