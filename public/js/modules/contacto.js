/**
 * Módulo para la página de Contacto
 * - Validación cliente sincronizada con ContactValidator (PHP)
 * - Estados visuales de error con clases existentes y prevención de doble envío
 */

export function initContactoPage() {
  const contactForm = document.getElementById('contact-form');
  if (!contactForm) {
    return;
  }

  const submitBtn = document.getElementById('contact-submit-btn');
  const submitLabel = document.getElementById('contact-submit-label');
  const nombreInput = document.getElementById('nombre');
  const emailInput = document.getElementById('email');
  const telefonoInput = document.getElementById('telefono');
  const asuntoSelect = document.getElementById('asunto');
  const mensajeInput = document.getElementById('mensaje');
  const datosCheckbox = document.getElementById('datos');

  function setError(targetEl, errorId, message) {
    const errEl = document.getElementById(errorId);
    if (targetEl) {
      if (message) {
        targetEl.classList.add('border-error');
        targetEl.classList.remove('border-outline-variant');
      } else {
        targetEl.classList.remove('border-error');
        targetEl.classList.add('border-outline-variant');
      }
    }
    if (errEl) {
      if (message) {
        errEl.textContent = message;
        errEl.classList.remove('hidden');
      } else {
        errEl.textContent = '';
        errEl.classList.add('hidden');
      }
    }
  }

  function validateField(field) {
    if (!field) return true;

    if (field === nombreInput) {
      const val = nombreInput.value.trim().replace(/\s+/g, ' ');
      const err =
        val.length < 3 || !/^[\p{L}\p{M}][\p{L}\p{M}\s'.\-]+$/u.test(val)
          ? 'Ingresa tu nombre completo usando solo letras.'
          : '';
      setError(nombreInput, 'error-contact-nombre', err);
      return err === '';
    }

    if (field === emailInput) {
      const val = emailInput.value.trim();
      const err = !/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(val)
        ? 'Ingresa un correo electrónico válido para poder responderte.'
        : '';
      setError(emailInput, 'error-contact-email', err);
      return err === '';
    }

    if (field === telefonoInput) {
      const compact = telefonoInput.value.trim().replace(/[\s\-().]+/g, '');
      let err = '';
      if (compact !== '') {
        const ok =
          /^\+?593[2-9]\d{7,8}$/.test(compact) ||
          /^0[2-9]\d{7,8}$/.test(compact) ||
          /^\+[1-9]\d{7,14}$/.test(compact);
        if (!ok) {
          err = 'Ingresa un teléfono ecuatoriano válido (ej. 099 123 4567).';
        }
      }
      setError(telefonoInput, 'error-contact-telefono', err);
      return err === '';
    }

    if (field === asuntoSelect) {
      const err = !asuntoSelect.value ? 'Selecciona un asunto institucional de la lista.' : '';
      setError(asuntoSelect, 'error-contact-asunto', err);
      return err === '';
    }

    if (field === mensajeInput) {
      const len = mensajeInput.value.trim().length;
      let err = '';
      if (len < 10) {
        err = 'Escribe un mensaje con al menos 10 caracteres.';
      } else if (len > 1500) {
        err = 'El mensaje no debe superar los 1500 caracteres.';
      }
      setError(mensajeInput, 'error-contact-mensaje', err);
      return err === '';
    }

    if (field === datosCheckbox) {
      const wrap = datosCheckbox.closest('div')?.parentElement || null;
      const err = !datosCheckbox.checked
        ? 'Debes aceptar el tratamiento de datos personales para enviar tu mensaje.'
        : '';
      setError(wrap, 'error-contact-datos', err);
      return err === '';
    }

    return true;
  }

  [nombreInput, emailInput, telefonoInput, asuntoSelect, mensajeInput].forEach((field) => {
    if (!field) return;
    field.addEventListener('blur', () => {
      if (field.value.trim() !== '') {
        validateField(field);
      }
    });
    field.addEventListener('input', () => {
      if (field.classList.contains('border-error')) {
        validateField(field);
      }
    });
  });

  if (asuntoSelect) {
    asuntoSelect.addEventListener('change', () => validateField(asuntoSelect));
  }

  if (datosCheckbox) {
    datosCheckbox.addEventListener('change', () => validateField(datosCheckbox));
  }

  contactForm.addEventListener('submit', (event) => {
    const ordered = [
      nombreInput,
      emailInput,
      telefonoInput,
      asuntoSelect,
      mensajeInput,
      datosCheckbox,
    ];
    let firstInvalid = null;

    ordered.forEach((field) => {
      if (!validateField(field) && !firstInvalid) {
        firstInvalid = field;
      }
    });

    if (firstInvalid) {
      event.preventDefault();
      if (typeof firstInvalid.focus === 'function') {
        firstInvalid.focus();
      }
      return;
    }

    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.classList.add('cursor-not-allowed');
    }
    if (submitLabel) {
      submitLabel.textContent = 'Enviando mensaje...';
    }
  });
}
