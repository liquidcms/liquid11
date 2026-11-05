/**
 * @file
 * Starts the homepage Revolution slider with the D7 dexp_layerslider settings.
 */
(function ($, Drupal, once) {
  Drupal.behaviors.evolveLayerslider = {
    attach(context) {
      once('evolve-layerslider', '#dexp-layerslider .layerslider-banner', context).forEach((el) => {
        $(el).css({ opacity: 1 }).revolution({
          delay: 9000,
          startheight: 500,
          startwidth: 1170,
          navigationType: 'bullet',
          navigationArrows: 'verticalcentered',
          navigationStyle: 'preview4',
          navigationHAlign: 'center',
          navigationVAlign: 'bottom',
          navigationHOffset: 0,
          navigationVOffset: 20,
          soloArrowLeftHalign: 'left',
          soloArrowLeftValign: 'center',
          soloArrowLeftHOffset: 20,
          soloArrowLeftVOffset: 0,
          soloArrowRightHalign: 'right',
          soloArrowRightValign: 'center',
          soloArrowRightHOffset: 20,
          soloArrowRightVOffset: 0,
          touchenabled: 'on',
          onHoverStop: 'on',
          stopAtSlide: -1,
          stopAfterLoops: -1,
          hideCaptionAtLimit: 0,
          hideAllCaptionAtLilmit: 0,
          hideSliderAtLimit: 0,
          fullWidth: 'on',
          fullScreen: 'off',
          lazyLoad: 'on',
          timer: 'bottom',
          shuffle: 'off',
          dottedOverlay: 'none',
          parallaxLevels: [10, 20, 30, 40, 50, 60, 70, 80, 90, 100],
        });
      });
    },
  };
})(jQuery, Drupal, once);
