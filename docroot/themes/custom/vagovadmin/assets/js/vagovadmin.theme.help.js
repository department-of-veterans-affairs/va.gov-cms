/**
 * @file
 * JavaScript behaviors for Bootstrap element help text (tooltip).
 *
 * @see js/webform.element.help.js
 */

(function ($, Drupal) {

  'use strict';

  // @see http://bootstrapdocs.com/v3.0.3/docs/javascript/#tooltips-usage
  Drupal.vagovadminTheme = Drupal.vagovadminTheme || {};
  Drupal.vagovadminTheme.elementHelpIcon = Drupal.vagovadminTheme.elementHelpIcon || {};
  Drupal.vagovadminTheme.elementHelpIcon.options = Drupal.vagovadminTheme.elementHelpIcon.options || {
    trigger: 'click',
    position: {
      my: "left bottom",
      at: "left center",
      collision: "flipfit flip"
    },
    delay: 100
  };

  /**
   * Bootstrap element help icon.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.vagovadminThemeElementHelpIcon = {
    attach: function (context) {
      $(context).find('.proofing-element-help').once('proofing-element-help').each(function () {
        var $link = $(this);

        var options = $.extend({
          content: $link.attr('data-proofing-help'),
          items: '[data-proofing-help]',
          title: $link.attr('data-proofing-help-title'),
          html: true
        }, Drupal.vagovadminTheme.elementHelpIcon.options);

        $link.tooltip(options)
          .on('click', function (event) {
            // Prevent click from toggling <label>s wrapped around help.
            event.preventDefault();
          });
      });
    }
  };

})(jQuery, Drupal);
