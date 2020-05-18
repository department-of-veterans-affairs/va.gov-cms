/**
 * @file
 */

(function ($, Drupal) {
  Drupal.behaviors.vaGovLearnMorePattern = {
    attach: function (context, settings) {
      // @todo: add accessibility features: make learn more section navigable by keyboard.
      $('.description > .learn-more', context).on('click', function (e) {
        e.preventDefault();
        $(e.target).toggleClass('open');
        $(e.target).closest('.description').find('.learn-more__info').toggleClass('show');
      });
    }
  };

})(jQuery, window.Drupal);
