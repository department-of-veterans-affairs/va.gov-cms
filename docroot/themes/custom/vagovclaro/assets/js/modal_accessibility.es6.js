/**
 * @file
 * Modal_accessibility behaviors.
 */

(($, Drupal) => {
  /**
   * Attaches modal accessibility behaviors.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.vagovmodalAccessibility = {
    attach() {
      $(".ui-dialog.entity-browser-modal").attr("tabindex", "0");
      $(".ui-dialog-titlebar-close").focus();
    },
  };
})(jQuery, Drupal);
