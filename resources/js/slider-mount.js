(function () {
  'use strict';

  function parseBool(v) { return String(v) === 'true'; }
  function parseNumber(v, fallback) { var n = Number(v); return Number.isFinite(n) ? n : fallback; }

  function readConfigFromContainer(swiperEl) {
    if (!swiperEl) return {};
    return {
      slideEffect: swiperEl.dataset.effect || '',
      speed: parseNumber(swiperEl.dataset.speed, 0),
      delay: parseNumber(swiperEl.dataset.delay, 0),
      stopOnLastSlide: parseBool(swiperEl.dataset.stopOnLast),
      prevSlideMessage: swiperEl.dataset.prevSlideMessage || '',
      nextSlideMessage: swiperEl.dataset.nextSlideMessage || ''
    };
  }

  function initSwiperForContainer(swiperEl) {
    if (typeof Swiper === 'undefined') return;
    if (swiperEl._sliderInitialized) return; // guard against double init
    var cfg = readConfigFromContainer(swiperEl);
    try {
      if (swiperEl.swiper && typeof swiperEl.swiper.destroy === 'function') {
        swiperEl.swiper.destroy(true, true);
      }
      var instance = new Swiper(swiperEl, {
        autoHeight: true,
        effect: cfg.slideEffect || undefined,
        a11y: {
          prevSlideMessage: cfg.prevSlideMessage || undefined,
          nextSlideMessage: cfg.nextSlideMessage || undefined,
        },
        keyboard: {
          enabled: true,
          onlyInViewport: true
        },
        pagination: {
          el: swiperEl.querySelector('.swiper-pagination'),
          clickable: true,
          renderBullet: function (index, className) { return '<span class="' + className + '"></span>'; }
        },
        navigation: {
          nextEl: swiperEl.querySelector('.swiper-button-next'),
          prevEl: swiperEl.querySelector('.swiper-button-prev'),
          addIcons: false
        },
        speed: cfg.speed,
        autoplay: { delay: cfg.delay, disableOnInteraction: true, stopOnLastSlide: !!cfg.stopOnLastSlide }
      });
      swiperEl._sliderInstance = instance;
      swiperEl._sliderInitialized = true;
    } catch (e) {
      // eslint-disable-next-line no-console
      console.error('SliderHome init error', e);
    }
  }

  function mountAll() {
    var mounts = document.querySelectorAll('.slider-home-mount');
    if (!mounts || mounts.length === 0) return;
    mounts.forEach(function (mountEl) {
      var swiperEl = mountEl.querySelector('.slider-home-swiper');
      if (!swiperEl) return;
      initSwiperForContainer(swiperEl);
    });
  }

  if (document.readyState === 'complete') {
    mountAll();
  } else {
    window.addEventListener('load', mountAll);
  }
})();
