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



function setButtonLoading(button, isLoading) {
  if (!button) return;
  button.classList.toggle('loading', isLoading);
}

AOS.init({
  once: true,
  disable: function () {
    return window.innerWidth < 992;
  }
});

document.addEventListener('click', function (e) {
  const btn = e.target.closest('.btn-consultar');
  if (!btn) return;

  // ⛔ Si es botón laboral, no seguimos esta lógica
  if (btn.classList.contains('btn-postular')) return;

  const card = btn.closest('.service-card');
  if (!card) return;

  const title = card.querySelector('h4')?.innerText.trim();
  if (!title) return;

  const emailTo = btn.dataset.email || 'presupuesto@teacompanamos.com.ar';
  const unidad  = btn.dataset.unidad || 'acompanamiento';
 
  const params = new URLSearchParams({
    service: title,
    to: emailTo,
    unidad: unidad
  });

  window.location.href = `contact.html?${params.toString()}`;

});










