// assets/js/forms/form-validation.js

export function validateForm(form) {
  let valid = true;

  form.querySelectorAll('[required]').forEach(input => {
    if (!input.value.trim()) {
      input.classList.add('is-invalid');
      input.classList.remove('is-valid');
      valid = false;
    } else {
      input.classList.remove('is-invalid');
      input.classList.add('is-valid');
    }
  });

  return valid;
}

export function bindLiveValidation(form) {
  form.querySelectorAll('input, textarea').forEach(input => {
    input.addEventListener('input', () => {
      if (input.value.trim()) {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
      }
    });
  });
}
