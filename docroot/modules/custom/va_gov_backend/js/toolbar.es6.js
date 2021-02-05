/**
 * @file
 */

(($, Drupal) => {
  Drupal.behaviors.vaGovToolbar = {
    attach() {
      $(".toolbar-icon-content-release")
        .once("vaGovToolbar")
        .hover(
          () => {
            $(".toolbar-icon-content-release").addClass("show-tooltip");
          },
          () => {
            $(".toolbar-icon-content-release").removeClass("show-tooltip");
          }
        );
    },
  };
})(jQuery, window.Drupal);
