/**
 * @file
 */

(($, Drupal, Tippy) => {
  /**
   * Add tooltips to proofing help icon elements.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.vagovclaroThemeElementHelpIcon = {
    attach() {
      Tippy(".proofing-element-help", {
        content(reference) {
          return reference.getAttribute("data-proofing-help");
        },
        allowHTML: true,
        theme: "tippy_popover",
      });
    },
  };
})(jQuery, Drupal, window.tippy);
