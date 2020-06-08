/**
 * @file
 */

(function ($, Drupal) {
  Drupal.behaviors.vaGovSectionsAccordion = {
    attach: function (context, settings) {
      // Add aria-hidden attribute to all collapsed areas.
      $('.sections').find('.subsections').attr('aria-hidden', true).addClass('hidden');

      $('.sections .toggle', context).on('click', function (e) {
        e.preventDefault();
        $(e.target).toggleClass('open');
        $(e.target).closest('li').find('a').toggleClass('open');
        $(e.target).attr('aria-pressed', function (_, attr) { return !(attr === 'true') });
        $(e.target).attr('aria-expanded', function (_, attr) { return !(attr === 'true') });
        $(e.target).closest('li').find('.subsections').attr('aria-hidden', function (_, attr) { return !(attr === 'true') });
        $(e.target).closest('li').find('.subsections').toggleClass('hidden');
      });
    }
  };

})(jQuery, window.Drupal);
