/**
 * @file
 */

(($, Drupal) => {
  Drupal.behaviors.vaGovToolbar = {
    attach() {
      $(".toolbar-icon-content-release")
        .once("vaGovToolbar")
        .click(() => {
          $(".toolbar-icon-content-release").toggleClass("show-tooltip");
        });
    },
  };
})(jQuery, window.Drupal);
