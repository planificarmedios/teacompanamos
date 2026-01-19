// assets/js/forms/forms-init.js

import { initAjaxForm } from './ajax-form.js';
import { bindLiveValidation } from './form-validation.js';

document.addEventListener('DOMContentLoaded', () => {

  // ===== FORMULARIO SERVICIOS =====
  const serviceForm = document.getElementById('serviceForm');
  if (serviceForm) {
    bindLiveValidation(serviceForm);

    initAjaxForm({
      formId: 'serviceForm',
      successText: 'Solicitud enviada correctamente ✅',
      recaptchaAction: 'service'
    });
  }

  // ===== FORMULARIO CONTACTO =====
  const contactForm = document.getElementById('contactForm');
  if (contactForm) {
    bindLiveValidation(contactForm);

    initAjaxForm({
      formId: 'contactForm',
      successText: 'Mensaje enviado correctamente. Gracias ✅',
      useSpinner: true,
      recaptchaAction: 'contact'
    });
  }

  // ===== FORMULARIO LABORALES =====
  const laboralesForm = document.getElementById('laboralesForm');
  if (laboralesForm) {
    bindLiveValidation(laboralesForm);

    initAjaxForm({
      formId: 'laboralesForm',
      successText: 'Postulación enviada correctamente ✅',
      useSpinner: true,
      recaptchaAction: 'laborales'
    });
  }

});
