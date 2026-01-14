// assets/js/forms/ajax-form.js

export function initAjaxForm({
  formId,
  successText,
  errorText = 'Error al enviar ❌',
  onSuccess = null,
  useSpinner = false,
  spinnerButtonSelector = '.btn-spinner',
  recaptchaAction = 'submit'
}) {

  const form = document.getElementById(formId);
  if (!form || form.dataset.ajaxInit === 'true') return;

  form.dataset.ajaxInit = 'true';

  const submitBtn = form.querySelector(spinnerButtonSelector);
  let isSubmitting = false;

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    e.stopImmediatePropagation();

    if (isSubmitting) return;
    isSubmitting = true;

    // Toast "Enviando"
    const sendingToast = Toastify({
      text: "Enviando… ⏳",
      duration: -1,
      gravity: "top",
      position: "right",
      close: false,
      backgroundColor: "#ffc107"
    });
    sendingToast.showToast();

    if (useSpinner && submitBtn) {
      startButtonLoading(submitBtn);
    }

    // reCAPTCHA v3
    grecaptcha.ready(() => {
      grecaptcha
        .execute('6LfHBUosAAAAAMBFPfReIN_zB0xDcACh2CH1SMJz', {
          action: recaptchaAction
        })
        .then(token => {

          const formData = new FormData(form);
          formData.append('recaptcha_token', token);

          fetch(form.action, {
            method: form.method || 'POST',
            body: formData
          })
            .then(res => {
              if (!res.ok) throw new Error('Network error');
              return res.json();
            })
            .then(data => {
              if (!data.success) {
                throw new Error(data.message || 'Error');
              }

              sendingToast.hideToast();

              Toastify({
                text: successText,
                duration: 4000,
                gravity: "top",
                position: "right",
                close: true,
                backgroundColor: "#28a745"
              }).showToast();

              form.reset();
              onSuccess?.();
            })
            .catch(err => {
              sendingToast.hideToast();

              Toastify({
                text: errorText,
                duration: 4000,
                gravity: "top",
                position: "right",
                close: true,
                backgroundColor: "#dc3545"
              }).showToast();

              console.error(err);
            })
            .finally(() => {
              if (useSpinner && submitBtn) {
                stopButtonLoading(submitBtn);
              }
              isSubmitting = false;
            });

        });
    });

  });
}
