(function ($) {
  var stickyHeader, stickypoint, target, MOBILEBREAKPOINT = 991;
  'use strict';

  /* ==========================================================================
   exists - Check if an element exists
   ========================================================================== */
  function handleSpecialties() {
    if (!$(document).find('.medical-specialties > li').length) {
      return;
    }

    $(document).on('hover, click', '.medical-specialties > li a', function (e) {
      e.preventDefault();
    });

    $(document).on('hover, click', '.medical-specialties > li', function (e) {
      $(this).siblings().removeClass('active');
      $(this).addClass('active');
    });
  }

  /* ==========================================================================
   exists - Check if an element exists
   ========================================================================== */

  function exists(e) {
    return $(e).length > 0;
  }

  /* ==========================================================================
   handleMobileMenu
   ========================================================================== */

  function handleMobileMenu() {

    if ($(window).width() > MOBILEBREAKPOINT) {

      $('#mobile-menu').hide();
      $('#mobile-menu-trigger').removeClass('mobile-menu-opened').addClass('mobile-menu-closed');

    } else {

      if (!exists('#mobile-menu')) {

        $('#desktop-menu').clone().attr({
          id: 'mobile-menu',
          'class': ''
        }).insertAfter('#header');

        $('#mobile-menu > li > a, #mobile-menu > li > ul > li > a').each(function () {
          var $t = $(this);
          if ($t.next().hasClass('sub-menu') || $t.next().is('ul') || $t.next().is('.sf-mega')) {
            $t.append('<span class="fa fa-angle-down mobile-menu-submenu-arrow mobile-menu-submenu-closed"></span>');
          }
        });

        $('.mobile-menu-submenu-arrow').on('click', function (event) {
          var $t = $(this);
          if ($t.hasClass('mobile-menu-submenu-closed')) {
            $t.removeClass('mobile-menu-submenu-closed fa-angle-down').addClass('mobile-menu-submenu-opened fa-angle-up').parent().siblings('ul, .sf-mega').slideDown(300);
          } else {
            $t.removeClass('mobile-menu-submenu-opened fa-angle-up').addClass('mobile-menu-submenu-closed fa-angle-down').parent().siblings('ul, .sf-mega').slideUp(300);
          }
          event.preventDefault();
        });

        $('#mobile-menu li, #mobile-menu li a, #mobile-menu ul').attr('style', '');

      }

    }

  }

  /* ==========================================================================
   showHideMobileMenu
   ========================================================================== */

  function showHideMobileMenu() {

    $('#mobile-menu-trigger').on('click', function (event) {

      var $t = $(this),
        $n = $('#mobile-menu');

      if ($t.hasClass('mobile-menu-opened')) {
        $t.removeClass('mobile-menu-opened').addClass('mobile-menu-closed');
        $n.slideUp(300);
      } else {
        $t.removeClass('mobile-menu-closed').addClass('mobile-menu-opened');
        $n.slideDown(300);
      }
      event.preventDefault();

    });

  }

  /* ==========================================================================
   handleBackToTop
   ========================================================================== */

  function handleBackToTop() {

    $('#back-to-top').on('click', function () {
      $('html, body').animate({
        scrollTop: 0
      }, 'slow');
      return false;
    });

  }

  /* ==========================================================================
   showHidebackToTop
   ========================================================================== */

  function showHidebackToTop() {

    if ($(window).scrollTop() > $(window).height() / 2) {
      $('#back-to-top').removeClass('gone').addClass('visible');
    } else {
      $('#back-to-top').removeClass('visible').addClass('gone');
    }

  }

  /* ==========================================================================
   handleSearch
   ========================================================================== */

  function handleSearch() {

    $('#custom-search-button').on('click', function (e) {

      e.preventDefault();

      if (!$('#custom-search-button').hasClass('open')) {

        $('#custom-search-form').fadeIn();
        $('#custom-search-button').addClass('open');
        $('#custom-search-form #s').trigger('focus');

      } else {

        $('#custom-search-form').fadeOut();
        $('#custom-search-button').removeClass('open');

      }

    });

  }

  /* ==========================================================================
   handleAccordionsAndToogles
   ========================================================================== */

  function handleAccordions() {
    $(document).on('click', '.accordion .accordion-item', function (e) {
      e.preventDefault();
      if ($(this).next('div').is(':visible')) {
        $(this).removeClass('active').next('div').slideUp();
      } else {
        $(this).siblings('.accordion-item').removeClass('active');
        $(this).siblings('.accordion-item-content').slideUp('fast');
        $(this).addClass('active').next('div').slideToggle('fast');
      }
    });

    $('.accordion .accordion-item:first-child').trigger('click').addClass('active');
  }

  /* ==========================================================================
   handleStickyHeader
   ========================================================================== */

  function handleDoctorProfile() {

    $('.doctor-profile-summary-details-trigger').on('click', function (e) {
      e.preventDefault();
      $(this).children('i').toggleClass('fa-plus-circle fa-minus-circle');
      $(this).toggleClass('doctor-profile-summary-details-shown');
      $(this).next('.doctor-profile-summary-details').toggleClass('shown');
    });

  }

  /* ==========================================================================
   handleStickyHeader
   ========================================================================== */

  function handleSmoothScroll() {

    // Select all links with hashes
    $('a[href*="#"]')

      // Remove links that don't actually link to anything
      .not('[href="#"]').not('[href="#mt-popup-modal"]').not('[href="#0"]').on('click', function (event) {

        // On-page links
        if (
          location.pathname.replace(/^\//, '') === this.pathname.replace(/^\//, '') &&
          location.hostname === this.hostname
        ) {

          // Figure out element to scroll to
          target = $(this.hash);
          target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');

          // Does a scroll target exist?
          if (target.length) {

            // Only prevent default if animation is actually gonna happen
            event.preventDefault();
            $('html, body').animate({
              scrollTop: target.offset().top
            }, 1000, function () {

              // Callback after animation
              // Must change focus!
              var $target = $(target);
              $target.trigger('focus');
              if ($target.is(':focus')) { // Checking if the target was focused
                return false;
              } else {
                $target.attr('tabindex', '-1'); // Adding tabindex for elements not focusable
                $target.trigger('focus'); // Set focus again
              }
            });
          }
        }
      });

  }

  /* ==========================================================================
   handleSubMenuOffscreen
   ========================================================================== */
  function handleSubMenuOffscreen() {
    $('.menu-item').on('hover', function () {
      $(this).find('.dropdown-menu').offscreen({
        rightClass: 'right-edge',
        widthOffset: 40,
        smartResize: true
      });
    });
  }

  /* ==========================================================================
   handleStickyHeader
   ========================================================================== */

  stickyHeader = false;
  stickypoint = 0;

  if ($('body').hasClass('sticky-header')) {
    stickyHeader = true;
  }

  stickypoint = $('#header').outerHeight();

  function handleStickyHeader() {

    var b = document.documentElement,
      e = false;

    function f() {

      window.addEventListener('scroll', function (h) {

        if (!e) {
          e = true;
          setTimeout(d, 250);
        }
      }, false);

      window.addEventListener('load', function (h) {

        if (!e) {
          e = true;
          setTimeout(d, 250);
        }
      }, false);
    }

    function d() {

      var h = c();

      if (h >= stickypoint) {
        $('#header').addClass('stuck');
      } else {
        $('#header').removeClass('stuck');
      }

      e = false;
    }

    function c() {

      return window.pageYOffset || b.scrollTop;

    }

    f();

  }

  /* ==========================================================================
   When document is ready, do
   ========================================================================== */

  $(document).ready(function () {
    var slides = $('.rooms-slider .slides'),
      testimonialSlides = $('.testimonial-slider .slides');
    handleMobileMenu();
    showHideMobileMenu();
    handleSubMenuOffscreen();

    handleBackToTop();
    showHidebackToTop();

    handleSearch();

    handleAccordions();

    handleSmoothScroll();

    handleDoctorProfile();

    handleSpecialties();

    if (stickyHeader) {
      handleStickyHeader();
    }

    // Superfish - enhance pure CSS drop-down menus
    // http://users.tpg.com.au/j_birch/plugins/superfish/options/

    if ('undefined' !== typeof $.fn.superfish) {

      $('#desktop-menu').superfish({
        delay: 500,
        animation: {
          opacity: 'show',
          height: 'show'
        },
        speed: 100,
        cssArrows: false
      });

    }

    // SlickSlider - responsive slider
    // http://kenwheeler.github.io/slick

    if ('undefined' !== typeof $.fn.slick) {
      if (slides.length) {
        slides.each(function () {
          var $t = $(this);
          $t.slick({
            adaptiveHeight: true,
            autoplay: true,
            infinite: true,
            speed: 500,
            fade: true,
            cssEase: 'linear',
            arrows: false,
            dots: true,
            appendDots: $t.next()
          });
        });
      }

      if (testimonialSlides.length) {
        testimonialSlides.each(function () {
          var $t = $(this);
          $t.slick({
            adaptiveHeight: true,
            autoplay: true,
            infinite: true,
            speed: 500,
            fade: true,
            cssEase: 'linear',
            appendArrows: $t.next(),
            prevArrow: '<a class="slick-prev" href="#"><i class="fa fa-angle-left"></i></a>',
            nextArrow: '<a class="slick-next" href="#"><i class="fa fa-angle-right"></i></a>'
          });
        });
      }

      $('.medzone-slider').each(function () {

        var $t = $(this);

        var $slider = $t.find('.slides');

        var $sliderConfig = {
          fade: 'true' === $t.attr('data-medzone-slider-mode-fade') ? true : false,
          speed: $t.attr('data-medzone-slider-speed') ? parseInt($t.attr('data-medzone-slider-speed'), 10) : 500,
          autoplay: 'true' === $t.attr('data-medzone-slider-autoplay') ? true : false,
          infinite: 'true' === $t.attr('data-medzone-slider-loop') ? true : false,
          pager: 'true' === $t.attr('data-medzone-slider-enable-pager') ? true : false,
          controls: 'true' === $t.attr('data-medzone-slider-enable-controls') ? true : false
        };

        $slider.slick({

          adaptiveHeight: true,

          fade: $sliderConfig.fade,
          cssEase: 'linear',

          speed: $sliderConfig.speed,

          autoplay: $sliderConfig.autoplay,

          infinite: $sliderConfig.infinite,

          arrows: $sliderConfig.controls,
          appendArrows: $t.find('.medzone-slider-arrows'),
          prevArrow: '<a class="slick-prev" href="#"><i class="fa fa-angle-left"></i></a>',
          nextArrow: '<a class="slick-next" href="#"><i class="fa fa-angle-right"></i></a>',

          dots: $sliderConfig.pager,
          appendDots: $t.find('.medzone-slider-dots')

        });

      });
    }

  });

  /* ==========================================================================
   When the window is scrolled, do
   ========================================================================== */

  $(window).on('scroll', function () {

    showHidebackToTop();

    if (stickyHeader) {
      handleStickyHeader();
    }

  });

  /* ==========================================================================
   When the window is resized, do
   ========================================================================== */

  $(window).resize(function () {

    handleMobileMenu();

    if (stickyHeader) {
      handleStickyHeader();
    }

  });

  $(document).on('epsilon-selective-refresh-ready', function () {
    var slides = $('.rooms-slider .slides'),
      testimonialSlides = $('.testimonial-slider .slides');
    handleSmoothScroll();
    handleDoctorProfile();

    if ('undefined' !== typeof $.fn.slick) {
      if (slides.length) {
        slides.each(function () {
          var $t = $(this);
          if (!$t.hasClass('slick-initialized')) {
            $t.slick({
              adaptiveHeight: true,
              autoplay: true,
              infinite: true,
              speed: 500,
              fade: true,
              cssEase: 'linear',
              arrows: false,
              dots: true,
              appendDots: $t.next()
            });
          }
        });
      }

      if (testimonialSlides.length) {
        testimonialSlides.each(function () {
          var $t = $(this);
          if (!$t.hasClass('slick-initialized')) {
            $t.slick({
              adaptiveHeight: true,
              autoplay: true,
              infinite: true,
              speed: 500,
              fade: true,
              cssEase: 'linear',
              appendArrows: $t.next(),
              prevArrow: '<a class="slick-prev" href="#"><i class="fa fa-angle-left"></i></a>',
              nextArrow: '<a class="slick-next" href="#"><i class="fa fa-angle-right"></i></a>'
            });
          }
        });
      }

      $('.medzone-slider').each(function () {

        var $t = $(this);

        var $slider = $t.find('.slides');

        var $sliderConfig = {
          fade: 'true' === $t.attr('data-medzone-slider-mode-fade') ? true : false,
          speed: $t.attr('data-medzone-slider-speed') ? parseInt($t.attr('data-medzone-slider-speed'), 10) : 500,
          autoplay: 'true' === $t.attr('data-medzone-slider-autoplay') ? true : false,
          infinite: 'true' === $t.attr('data-medzone-slider-loop') ? true : false,
          pager: 'true' === $t.attr('data-medzone-slider-enable-pager') ? true : false,
          controls: 'true' === $t.attr('data-medzone-slider-enable-controls') ? true : false
        };

        $slider.slick({

          adaptiveHeight: true,

          fade: $sliderConfig.fade,
          cssEase: 'linear',

          speed: $sliderConfig.speed,

          autoplay: $sliderConfig.autoplay,

          infinite: $sliderConfig.infinite,

          arrows: $sliderConfig.controls,
          appendArrows: $t.find('.medzone-slider-arrows'),
          prevArrow: '<a class="slick-prev" href="#"><i class="fa fa-angle-left"></i></a>',
          nextArrow: '<a class="slick-next" href="#"><i class="fa fa-angle-right"></i></a>',

          dots: $sliderConfig.pager,
          appendDots: $t.find('.medzone-slider-dots')

        });

      });
    }

  });

})(jQuery);