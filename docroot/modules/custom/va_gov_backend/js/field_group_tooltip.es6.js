/**
 * @file
 */

(($, Drupal, Tippy) => {
  Drupal.behaviors.vaGovTooltip = {
    attach() {
      Tippy(".tooltip-toggle", {
        content(reference) {
          const title = reference.getAttribute("title");
          reference.removeAttribute("title");
          return title;
        },
        theme: "tippy_popover tippy_popover_center",
        offset: "0, -12",
      });
    },
  };
})(jQuery, window.Drupal, window.tippy);
