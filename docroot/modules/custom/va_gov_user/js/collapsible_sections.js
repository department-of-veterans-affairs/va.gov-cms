/**
 * @file
 */

(function ($, Drupal) {
  Drupal.behaviors.vaGovCollapsibleSections = {
    attach: function (context, settings) {
      // @todo: add accessibility features: make sections navigable by keyboard.
      // Add aria-hidden attribute to all collapsed areas.
      $('.sections').find('.subsections').attr('aria-hidden', true).addClass('hidden');

      $('.sections .usa-accordion-button', context).on('click', function (e) {
        e.preventDefault();
        $(e.target).toggleClass('open');
        $(e.target).attr('aria-pressed', function (_, attr) { return !(attr === 'true') });
        $(e.target).attr('aria-expanded', function (_, attr) { return !(attr === 'true') });
        $(e.target).closest('li').find('.subsections').attr('aria-hidden', function (_, attr) { return !(attr === 'true') });
        $(e.target).closest('li').find('.subsections').toggleClass('hidden');
      });
    }
  };

})(jQuery, window.Drupal);
