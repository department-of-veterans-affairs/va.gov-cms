/**
 * @file
 * Anchor link behaviors.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Return the combined height of the admin toolbar & tray.
   *
   * @return {number}
   *   Height in pixels.
   */
  Drupal.getAdminToolbarHeight = function () {
    const toolbarHeight = $('#toolbar-bar').height() || 0;
    const tooltrayHeight = $('#toolbar-item-administration-tray.toolbar-tray-horizontal').height() || 0;
    return toolbarHeight + tooltrayHeight;
  };

  /**
   * Attaches anchor link behavior to links.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.vagovadminAnchorLinks = {
    attach: function (context) {
      $('a[href^="#"]').click(function (e) {
        e.preventDefault();

        var target = $(this).attr('href');
        var scrollToPosition = $(target).offset().top - (Drupal.getAdminToolbarHeight() + 10);

        $('html').animate({'scrollTop': scrollToPosition}, 500, function () {
          window.location.hash = '' + target;
          $('html').animate({'scrollTop': scrollToPosition}, 0);
        });
      });
    }
  };

})(jQuery, Drupal);
