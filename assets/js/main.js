/**
* Template Name: MediNest
* Template URL: https://bootstrapmade.com/medinest-bootstrap-hospital-template/
* Updated: Aug 11 2025 with Bootstrap v5.3.7
* Author: BootstrapMade.com
* License: https://bootstrapmade.com/license/
*/

(function() {
  "use strict";

  /**
   * Apply .scrolled class to the body as the page is scrolled down
   */
  function toggleScrolled() {
    const selectBody = document.querySelector('body');
    const selectHeader = document.querySelector('#header');
    if (!selectHeader.classList.contains('scroll-up-sticky') && !selectHeader.classList.contains('sticky-top') && !selectHeader.classList.contains('fixed-top')) return;
    window.scrollY > 100 ? selectBody.classList.add('scrolled') : selectBody.classList.remove('scrolled');
  }

  document.addEventListener('scroll', toggleScrolled);
  window.addEventListener('load', toggleScrolled);

  /**
   * Mobile nav toggle
   */
  const mobileNavToggleBtn = document.querySelector('.mobile-nav-toggle');

  function mobileNavToogle() {
    document.querySelector('body').classList.toggle('mobile-nav-active');
    mobileNavToggleBtn.classList.toggle('bi-list');
    mobileNavToggleBtn.classList.toggle('bi-x');
  }
  if (mobileNavToggleBtn) {
    mobileNavToggleBtn.addEventListener('click', mobileNavToogle);
  }

  /**
   * Hide mobile nav on same-page/hash links
   */
  document.querySelectorAll('#navmenu a').forEach(navmenu => {
    navmenu.addEventListener('click', () => {
      if (document.querySelector('.mobile-nav-active')) {
        mobileNavToogle();
      }
    });

  });

  /**
   * Toggle mobile nav dropdowns
   */
  document.querySelectorAll('.navmenu .toggle-dropdown').forEach(navmenu => {
    navmenu.addEventListener('click', function(e) {
      e.preventDefault();
      this.parentNode.classList.toggle('active');
      this.parentNode.nextElementSibling.classList.toggle('dropdown-active');
      e.stopImmediatePropagation();
    });
  });

 
  /**
   * Scroll top button
   */
  let scrollTop = document.querySelector('.scroll-top');

  function toggleScrollTop() {
    if (scrollTop) {
      window.scrollY > 100 ? scrollTop.classList.add('active') : scrollTop.classList.remove('active');
    }
  }
  scrollTop.addEventListener('click', (e) => {
    e.preventDefault();
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  });

  window.addEventListener('load', toggleScrollTop);
  document.addEventListener('scroll', toggleScrollTop);

  /**
   * Animation on scroll function and init
   */
  function aosInit() {
  if (window.innerWidth >= 992) { // solo desktop
    AOS.init({
      duration: 600,
      easing: 'ease-in-out',
      once: true,
      mirror: false
    });
  }
}

window.addEventListener('load', aosInit);


  /**
   * Initiate glightbox
   */
  const glightbox = GLightbox({
    selector: '.glightbox'
  });

  /**
   * Initiate Pure Counter
   */
  
  //   if (window.innerWidth >= 992) {
  //   new PureCounter();
  // }


  /**
   * Init isotope layout and filters
   */
  if (window.innerWidth >= 992) {
  document.querySelectorAll('.isotope-layout').forEach(function(isotopeItem) {
    let layout = isotopeItem.getAttribute('data-layout') ?? 'masonry';
    let filter = isotopeItem.getAttribute('data-default-filter') ?? '*';
    let sort = isotopeItem.getAttribute('data-sort') ?? 'original-order';

    let initIsotope;
    imagesLoaded(isotopeItem.querySelector('.isotope-container'), function() {
      initIsotope = new Isotope(isotopeItem.querySelector('.isotope-container'), {
        itemSelector: '.isotope-item',
        layoutMode: layout,
        filter: filter,
        sortBy: sort
      });
    });

    isotopeItem.querySelectorAll('.isotope-filters li').forEach(function(filters) {
      filters.addEventListener('click', function() {
        isotopeItem.querySelector('.isotope-filters .filter-active').classList.remove('filter-active');
        this.classList.add('filter-active');
        initIsotope.arrange({
          filter: this.getAttribute('data-filter')
        });
      }, false);
    });
  });
}


  /**
   * Init swiper sliders
   */
  function initSwiper() {
    document.querySelectorAll(".init-swiper").forEach(function(swiperElement) {
      let config = JSON.parse(
        swiperElement.querySelector(".swiper-config").innerHTML.trim()
      );

      if (swiperElement.classList.contains("swiper-tab")) {
        initSwiperWithCustomPagination(swiperElement, config);
      } else {
        new Swiper(swiperElement, config);
      }
    });
  }

  window.addEventListener("load", () => {
  if (document.querySelector('.init-swiper')) {
    initSwiper();
  }
});


  /**
   * Frequently Asked Questions Toggle
   */
  document.querySelectorAll('.faq-item h3, .faq-item .faq-toggle, .faq-item .faq-header').forEach((faqItem) => {
    faqItem.addEventListener('click', () => {
      faqItem.parentNode.classList.toggle('faq-active');
    });
  });

})();

window.addEventListener('load', () => {

  /* =====================
     Preloader
  ====================== */
  const preloader = document.querySelector('#preloader');
  if (preloader) {
    preloader.remove();
  }

});

function startButtonLoading(button) {
  if (!button) return;

  const text = button.querySelector('.btn-text');
  const loader = button.querySelector('.btn-loading');

  button.disabled = true;

  if (button.dataset.loadingText && text) {
    text.dataset.originalText = text.innerText;
    text.innerText = button.dataset.loadingText;
  }

  text && text.classList.add('d-none');
  loader && loader.classList.remove('d-none');
}

function stopButtonLoading(button) {
  if (!button) return;

  const text = button.querySelector('.btn-text');
  const loader = button.querySelector('.btn-loading');

  button.disabled = false;

  if (text?.dataset.originalText) {
    text.innerText = text.dataset.originalText;
  }

  text && text.classList.remove('d-none');
  loader && loader.classList.add('d-none');
}

document.addEventListener('DOMContentLoaded', () => {

  // CONTACTO → spinner en botón
  initAjaxForm({
    formId: 'contactForm',
    successText: 'Mensaje enviado correctamente. Gracias ✅',
    useSpinner: true
  });

  // SERVICIOS → sin spinner
  initAjaxForm({
    formId: 'serviceForm',
    successText: 'Solicitud enviada correctamente ✅',
    onSuccess: () => {
      const modalEl = document.getElementById('serviceModal');
      bootstrap.Modal.getInstance(modalEl)?.hide();
    }
  });

});

function initAjaxForm({
  formId,
  successText,
  errorText = 'Error al enviar ❌',
  onSuccess = null,
  useSpinner = false,
  spinnerButtonSelector = '.btn-spinner'
}) {
  
  const form = document.getElementById(formId);
  if (!form || form.dataset.ajaxInit === 'true') return;

  form.dataset.ajaxInit = 'true';

  const submitBtn = form.querySelector(spinnerButtonSelector);
 
  form.addEventListener('submit', function (e) {

    e.preventDefault();
    e.stopImmediatePropagation();

    if (useSpinner) {
      startButtonLoading(submitBtn);
    }

    const formData = new FormData(form);

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

        Toastify({
          text: successText,
          duration: 4000,
          gravity: "top",
          position: "right",
          close: true,
          backgroundColor: "#28a745"
        }).showToast();

        form.reset();

        if (typeof onSuccess === 'function') {
          onSuccess();
        }

      })
      .catch(err => {

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
        if (useSpinner) {
          stopButtonLoading(submitBtn);
        }
      });

    return false;
  });
}

function setButtonLoading(button, isLoading) {
  if (!button) return;
  button.classList.toggle('loading', isLoading);
}

window.openServiceModal = function (button, email) {

  // quitar foco (mobile)
  button.blur();

  const card = button.closest('.service-card');
  const title = card.querySelector('h4').innerText;

  document.getElementById('serviceSubject').value = title;
  document.getElementById('serviceTo').value = email;

  const modalEl = document.getElementById('serviceModal');
  const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
  modal.show();
};
