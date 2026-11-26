/**
 * Módulo de interacciones para la página Agendar Cita
 * - Filtrado dinámico de médicos según la especialidad seleccionada (o sin preferencia)
 * - Navegación validada de 4 pasos (Atención, Fecha y turno, Datos paciente, Revisión)
 * - Validación cliente sincronizada con el backend PHP (Cédula ecuatoriana módulo 10)
 * - Actualización en vivo del resumen en el Paso 4 y prevención de doble envío
 */

export function initAgendarCitaPage() {
  const form = document.getElementById('appointment-form');
  const stepPanels = Array.from(document.querySelectorAll('[data-step-panel]'));

  if (!form || stepPanels.length === 0) {
    return;
  }

  const stepTriggers = Array.from(document.querySelectorAll('[data-step-trigger]'));
  const gotoButtons = Array.from(document.querySelectorAll('[data-goto-step]'));
  const progressBar = document.getElementById('appointment-progress-bar');

  // Controles del formulario
  const specialtySelect = document.getElementById('field-specialty');
  const doctorSelect = document.getElementById('field-doctor');
  const doctorHint = document.getElementById('doctor-filter-hint');
  const dateInput = document.getElementById('field-date');
  const shiftRadios = Array.from(document.querySelectorAll('input[name="shift-preference"]'));
  const shiftCardManana = document.querySelector('[data-shift-card="manana"]');
  const shiftCardTarde = document.querySelector('[data-shift-card="tarde"]');
  const fullNameInput = document.getElementById('field-fullname');
  const documentInput = document.getElementById('field-document');
  const phoneInput = document.getElementById('field-phone');
  const emailInput = document.getElementById('field-email');
  const commentsInput = document.getElementById('field-comments');
  const privacyInput = document.getElementById('field-privacy');
  const submitBtn = document.getElementById('appointment-submit-btn');
  const submitLabel = document.getElementById('appointment-submit-label');

  // Nodos del resumen en el Paso 4
  const summarySpecialty = document.getElementById('summary-specialty');
  const summaryDoctor = document.getElementById('summary-doctor');
  const summaryDate = document.getElementById('summary-date');
  const summaryShift = document.getElementById('summary-shift');
  const summaryFullname = document.getElementById('summary-fullname');
  const summaryDocument = document.getElementById('summary-document');
  const summaryPhone = document.getElementById('summary-phone');
  const summaryEmail = document.getElementById('summary-email');
  const summaryReason = document.getElementById('summary-reason');

  // Capturar catálogo completo de médicos desde el DOM inicial para filtrado multi-navegador
  const initialSelectedDoctor = doctorSelect ? doctorSelect.value : '';
  const allDoctorCatalog = doctorSelect
    ? Array.from(doctorSelect.options)
        .filter((opt) => opt.value !== '')
        .map((opt) => ({
          value: opt.value,
          label: (opt.textContent || '').trim(),
          specialties: (opt.getAttribute('data-specialties') || '')
            .split(/\s+/)
            .filter(Boolean),
        }))
    : [];

  let currentStep = 1;

  const progressWidthClasses = {
    1: 'w-1/4',
    2: 'w-2/4',
    3: 'w-3/4',
    4: 'w-full',
  };

  function setFieldError(inputEl, errorId, message) {
    const errEl = document.getElementById(errorId);
    if (inputEl) {
      if (message) {
        inputEl.classList.add('border-error');
        inputEl.classList.remove('border-outline-variant');
      } else {
        inputEl.classList.remove('border-error');
        inputEl.classList.add('border-outline-variant');
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

  function isValidEcuadorianCedula(ced) {
    if (!/^\d{10}$/.test(ced)) return false;
    const prov = Number(ced.slice(0, 2));
    if ((prov < 1 || prov > 24) && prov !== 30) return false;
    const coeffs = [2, 1, 2, 1, 2, 1, 2, 1, 2];
    let sum = 0;
    for (let i = 0; i < 9; i += 1) {
      let val = Number(ced[i]) * coeffs[i];
      if (val >= 10) val -= 9;
      sum += val;
    }
    const check = (10 - (sum % 10)) % 10;
    return check === Number(ced[9]);
  }

  function validateDocumentValue(raw) {
    const cleaned = (raw || '').trim().replace(/[\s\-]+/g, '');
    if (!cleaned) {
      return 'Ingresa el número de cédula ecuatoriana del paciente.';
    }
    if (!/^\d{10}$/.test(cleaned)) {
      return 'Ingresa una cédula ecuatoriana válida de 10 dígitos.';
    }
    return isValidEcuadorianCedula(cleaned)
      ? ''
      : 'El número de cédula ecuatoriana no es válido. Verifica los 10 dígitos.';
  }

  function validatePhoneValue(raw) {
    const compact = (raw || '').trim().replace(/[\s\-().]+/g, '');
    if (!compact) {
      return 'Ingresa un número de teléfono móvil o convencional de contacto.';
    }
    if (/^\+?593[2-9]\d{7,8}$/.test(compact)) return '';
    if (/^0[2-9]\d{7,8}$/.test(compact)) return '';
    if (/^\+[1-9]\d{7,14}$/.test(compact)) return '';
    return 'Ingresa un teléfono ecuatoriano válido (ej. 098 765 4321) o con código internacional (+...).';
  }

  function filterDoctorsBySpecialty(preserveValue) {
    if (!doctorSelect || !specialtySelect) return;

    const specValue = specialtySelect.value || '';
    const desiredDoctor =
      typeof preserveValue === 'string' ? preserveValue : doctorSelect.value || '';

    const matchingDoctors =
      specValue !== ''
        ? allDoctorCatalog.filter((d) => d.specialties.includes(specValue))
        : [];

    doctorSelect.innerHTML = '';

    const anyOpt = document.createElement('option');
    anyOpt.value = '';
    anyOpt.textContent = 'Cualquier especialista disponible (Sin preferencia)';
    doctorSelect.appendChild(anyOpt);

    let matchedSelection = false;
    matchingDoctors.forEach((doc) => {
      const opt = document.createElement('option');
      opt.value = doc.value;
      opt.textContent = doc.label;
      opt.setAttribute('data-specialties', doc.specialties.join(' '));
      if (desiredDoctor !== '' && doc.value === desiredDoctor) {
        opt.selected = true;
        matchedSelection = true;
      }
      doctorSelect.appendChild(opt);
    });

    if (!matchedSelection) {
      doctorSelect.value = '';
    }

    if (doctorHint) {
      if (specValue === '') {
        doctorHint.textContent =
          'Selecciona primero una especialidad para ver sus médicos asignados, o mantén «Sin preferencia».';
      } else if (matchingDoctors.length === 0) {
        doctorHint.textContent =
          'Sin preferencia activa: Admisión asignará el especialista disponible para esta especialidad.';
      } else if (matchingDoctors.length === 1) {
        doctorHint.textContent =
          '1 especialista disponible en esta especialidad (o puedes dejar «Sin preferencia»).';
      } else {
        doctorHint.textContent = `${matchingDoctors.length} especialistas disponibles en esta especialidad (o puedes dejar «Sin preferencia»).`;
      }
    }
  }

  function validateStep(stepNum, focusFirstError = true) {
    let firstInvalid = null;

    if (stepNum === 1) {
      const specVal = specialtySelect ? specialtySelect.value : '';
      if (!specVal) {
        setFieldError(
          specialtySelect,
          'error-specialty',
          'Selecciona una especialidad o servicio médico.'
        );
        firstInvalid = firstInvalid || specialtySelect;
      } else {
        setFieldError(specialtySelect, 'error-specialty', '');
      }
      setFieldError(doctorSelect, 'error-doctor', '');
    } else if (stepNum === 2) {
      const dateVal = dateInput ? dateInput.value : '';
      const minDate = dateInput ? dateInput.getAttribute('min') || '' : '';
      const maxDate = dateInput ? dateInput.getAttribute('max') || '' : '';

      if (!dateVal) {
        setFieldError(dateInput, 'error-date', 'Selecciona una fecha preferida para tu consulta.');
        firstInvalid = firstInvalid || dateInput;
      } else if (minDate && dateVal < minDate) {
        setFieldError(
          dateInput,
          'error-date',
          'La fecha de la cita no puede ser anterior al día de hoy.'
        );
        firstInvalid = firstInvalid || dateInput;
      } else if (maxDate && dateVal > maxDate) {
        setFieldError(
          dateInput,
          'error-date',
          'Solo puedes solicitar citas hasta con 90 días de anticipación.'
        );
        firstInvalid = firstInvalid || dateInput;
      } else {
        setFieldError(dateInput, 'error-date', '');
      }

      const checkedShift = shiftRadios.find((r) => r.checked);
      if (!checkedShift) {
        setFieldError(null, 'error-shift', 'Selecciona una franja horaria.');
        firstInvalid = firstInvalid || shiftRadios[0];
      } else {
        setFieldError(null, 'error-shift', '');
      }
    } else if (stepNum === 3) {
      const nameVal = fullNameInput ? fullNameInput.value.trim().replace(/\s+/g, ' ') : '';
      const words = nameVal ? nameVal.split(' ') : [];
      if (nameVal.length < 4 || words.length < 2 || !/^[\p{L}\p{M}][\p{L}\p{M}\s'.\-]+$/u.test(nameVal)) {
        setFieldError(
          fullNameInput,
          'error-fullname',
          'Ingresa al menos un nombre y un apellido usando solo letras.'
        );
        firstInvalid = firstInvalid || fullNameInput;
      } else {
        setFieldError(fullNameInput, 'error-fullname', '');
      }

      const docErr = validateDocumentValue(documentInput ? documentInput.value : '');
      setFieldError(documentInput, 'error-document', docErr);
      if (docErr) firstInvalid = firstInvalid || documentInput;

      const phoneErr = validatePhoneValue(phoneInput ? phoneInput.value : '');
      setFieldError(phoneInput, 'error-phone', phoneErr);
      if (phoneErr) firstInvalid = firstInvalid || phoneInput;

      const emailVal = emailInput ? emailInput.value.trim() : '';
      if (emailVal !== '' && !/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(emailVal)) {
        setFieldError(
          emailInput,
          'error-email',
          'Ingresa un correo electrónico válido o déjalo vacío.'
        );
        firstInvalid = firstInvalid || emailInput;
      } else {
        setFieldError(emailInput, 'error-email', '');
      }

      const reasonVal = commentsInput ? commentsInput.value.trim() : '';
      if (reasonVal.length > 500) {
        setFieldError(
          commentsInput,
          'error-comments',
          'El motivo o comentario no debe superar los 500 caracteres.'
        );
        firstInvalid = firstInvalid || commentsInput;
      } else {
        setFieldError(commentsInput, 'error-comments', '');
      }

      const privacyWrap = privacyInput ? privacyInput.closest('div') : null;
      if (!privacyInput || !privacyInput.checked) {
        setFieldError(
          privacyWrap,
          'error-privacy',
          'Debes aceptar la política de protección de datos personales para continuar.'
        );
        firstInvalid = firstInvalid || privacyInput;
      } else {
        setFieldError(privacyWrap, 'error-privacy', '');
      }
    }

    if (firstInvalid && focusFirstError && typeof firstInvalid.focus === 'function') {
      firstInvalid.focus();
    }

    return firstInvalid === null;
  }

  function updateSummary() {
    const specLabel =
      specialtySelect && specialtySelect.value && specialtySelect.selectedOptions[0]
        ? specialtySelect.selectedOptions[0].textContent.trim()
        : 'Pendiente de seleccionar';

    const docLabel =
      doctorSelect && doctorSelect.value && doctorSelect.selectedOptions[0]
        ? doctorSelect.selectedOptions[0].textContent.trim()
        : 'Sin preferencia (Cualquier especialista disponible)';

    const selectedDate =
      dateInput && dateInput.value ? dateInput.value : 'Pendiente de seleccionar';

    const checkedShiftRadio = shiftRadios.find((r) => r.checked);
    const selectedShift = checkedShiftRadio
      ? checkedShiftRadio.value
      : 'Mañana (08:00 - 12:00)';

    const fullName =
      fullNameInput && fullNameInput.value.trim() !== ''
        ? fullNameInput.value.trim()
        : 'Pendiente de ingresar';

    const docNumber =
      documentInput && documentInput.value.trim() !== ''
        ? documentInput.value.trim()
        : 'Pendiente de ingresar';

    const phone =
      phoneInput && phoneInput.value.trim() !== ''
        ? phoneInput.value.trim()
        : 'Pendiente de ingresar';

    const email =
      emailInput && emailInput.value.trim() !== ''
        ? `Correo: ${emailInput.value.trim()}`
        : 'Sin correo electrónico indicado';

    const reason = commentsInput ? commentsInput.value.trim() : '';

    if (summarySpecialty) summarySpecialty.textContent = specLabel;
    if (summaryDoctor) summaryDoctor.textContent = `Médico: ${docLabel}`;
    if (summaryDate) summaryDate.textContent = selectedDate;
    if (summaryShift) summaryShift.textContent = selectedShift;
    if (summaryFullname) summaryFullname.textContent = fullName;
    if (summaryDocument) summaryDocument.textContent = `Cédula: ${docNumber}`;
    if (summaryPhone) summaryPhone.textContent = `Teléfono / WhatsApp: ${phone}`;
    if (summaryEmail) summaryEmail.textContent = email;

    if (summaryReason) {
      if (reason !== '') {
        summaryReason.textContent = `Motivo / Comentarios: ${reason}`;
        summaryReason.classList.remove('hidden');
      } else {
        summaryReason.textContent = '';
        summaryReason.classList.add('hidden');
      }
    }
  }

  function updateShiftCards() {
    const checkedShiftRadio = shiftRadios.find((r) => r.checked);
    const isManana = !checkedShiftRadio || checkedShiftRadio.value.startsWith('Mañana');

    if (shiftCardManana) {
      shiftCardManana.className = isManana
        ? 'cursor-pointer relative p-4 rounded-xl transition-all flex items-center justify-between border bg-surface-container-highest border-primary'
        : 'cursor-pointer relative p-4 rounded-xl transition-all flex items-center justify-between border bg-surface-container-low border-outline-variant hover:bg-surface-container';
    }

    if (shiftCardTarde) {
      shiftCardTarde.className = !isManana
        ? 'cursor-pointer relative p-4 rounded-xl transition-all flex items-center justify-between border bg-surface-container-highest border-primary'
        : 'cursor-pointer relative p-4 rounded-xl transition-all flex items-center justify-between border bg-surface-container-low border-outline-variant hover:bg-surface-container';
    }
  }

  function setStep(stepNumber, skipValidation = false) {
    const targetStep = Number(stepNumber);
    if (targetStep < 1 || targetStep > 4) return;

    if (!skipValidation && targetStep > currentStep) {
      for (let s = currentStep; s < targetStep; s += 1) {
        if (!validateStep(s, true)) {
          if (currentStep !== s) {
            setStep(s, true);
          }
          return;
        }
      }
    }

    currentStep = targetStep;

    stepPanels.forEach((panel) => {
      const panelStep = Number(panel.getAttribute('data-step-panel'));
      if (panelStep === currentStep) {
        panel.classList.remove('hidden');
      } else {
        panel.classList.add('hidden');
      }
    });

    stepTriggers.forEach((btn) => {
      const btnStep = Number(btn.getAttribute('data-step-trigger'));
      const circle = btn.querySelector('[data-step-circle]');

      if (btnStep === currentStep) {
        btn.className =
          'flex items-center gap-3 text-left transition-all p-2 rounded-xl group focus-primary bg-surface-container-low text-primary';
      } else {
        btn.className =
          'flex items-center gap-3 text-left transition-all p-2 rounded-xl group focus-primary text-on-surface-variant hover:bg-surface-container-low';
      }

      if (circle) {
        if (btnStep === currentStep) {
          circle.className =
            'w-9 h-9 rounded-full font-title-md text-label-md flex items-center justify-center font-bold shrink-0 transition-transform group-hover:scale-105 bg-primary text-on-primary';
        } else if (btnStep < currentStep) {
          circle.className =
            'w-9 h-9 rounded-full font-title-md text-label-md flex items-center justify-center font-bold shrink-0 transition-transform group-hover:scale-105 bg-primary-container text-on-primary';
        } else {
          circle.className =
            'w-9 h-9 rounded-full font-title-md text-label-md flex items-center justify-center font-bold shrink-0 transition-transform group-hover:scale-105 bg-surface-container text-on-surface-variant';
        }
      }
    });

    if (progressBar) {
      progressBar.classList.remove('w-1/4', 'w-2/4', 'w-3/4', 'w-full');
      progressBar.classList.add(progressWidthClasses[currentStep] || 'w-1/4');
    }

    updateSummary();
  }

  // Navegación por pasos
  stepTriggers.forEach((btn) => {
    btn.addEventListener('click', () => {
      const step = Number(btn.getAttribute('data-step-trigger'));
      setStep(step, false);
    });
  });

  gotoButtons.forEach((btn) => {
    btn.addEventListener('click', () => {
      const step = Number(btn.getAttribute('data-goto-step'));
      setStep(step, false);
    });
  });

  // Cambio de especialidad -> filtra médicos inmediatamente
  if (specialtySelect) {
    specialtySelect.addEventListener('change', () => {
      filterDoctorsBySpecialty('');
      setFieldError(specialtySelect, 'error-specialty', '');
      updateSummary();
    });
  }

  [doctorSelect, dateInput, fullNameInput, documentInput, phoneInput, emailInput, commentsInput].forEach(
    (input) => {
      if (input) {
        input.addEventListener('input', updateSummary);
        input.addEventListener('change', updateSummary);
      }
    }
  );

  if (privacyInput) {
    privacyInput.addEventListener('change', () => {
      if (privacyInput.checked) {
        setFieldError(privacyInput.closest('div'), 'error-privacy', '');
      }
    });
  }

  shiftRadios.forEach((radio) => {
    radio.addEventListener('change', () => {
      updateShiftCards();
      updateSummary();
    });
  });

  // Validación integral antes de enviar al servidor y bloqueo de doble clic
  form.addEventListener('submit', (event) => {
    for (let s = 1; s <= 3; s += 1) {
      if (!validateStep(s, true)) {
        event.preventDefault();
        setStep(s, true);
        return;
      }
    }

    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.classList.add('cursor-not-allowed');
    }
    if (submitLabel) {
      submitLabel.textContent = 'Registrando solicitud...';
    }
  });

  // Inicializar estado (respetando médico preseleccionado por ?doctor= o reintento 422)
  filterDoctorsBySpecialty(initialSelectedDoctor);
  updateShiftCards();

  const initialStepAttr = Number(form.getAttribute('data-initial-step') || '1');
  setStep(initialStepAttr >= 1 && initialStepAttr <= 4 ? initialStepAttr : 1, true);
}
