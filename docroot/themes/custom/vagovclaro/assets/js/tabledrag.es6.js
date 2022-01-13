/**
 * @file
 * Extending Drupal Core's tabledrag.
 *
 */
(($, Drupal) => {
  $.extend(
    Drupal.theme,
    /** @lends Drupal.theme */ {
      /**
       * Constructs the table drag changed warning.
       *
       * @return {string}
       *   Markup for the warning.
       */
      tableDragChangedWarning() {
        return `<div class="tabledrag-changed-warning va-alert messages messages--warning" role="alert">${Drupal.theme(
          "tableDragChangedMarker"
        )} ${Drupal.t("You have unsaved changes.")}</div>`;
      },
    }
  );
})(jQuery, Drupal);
