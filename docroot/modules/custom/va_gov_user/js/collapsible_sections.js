/**
 * @file
 */

(function ($, Drupal) {
  Drupal.behaviors.vaGovCollapsibleSections = {
    attach: function (context, settings) {
      // @todo: add accessibility features: make sections navigable by keyboard.
      $('.sections .toggle', context).on('click', function (e) {
        e.preventDefault();
        $(e.target).toggleClass('open');
        $(e.target).closest('li').find('.item-list').toggleClass('show');
      });
    }
  };

})(jQuery, window.Drupal);
