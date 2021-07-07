/**
 * @file
 */

(($, Drupal, Tippy) => {
  Drupal.behaviors.vaGovTooltip = {
    attach(context) {
      $(document, context)
        .once("tooltip-toggle")
        .each(() => {
          Tippy(".tooltip-toggle", {
            content(reference) {
              const title = reference.getAttribute("title");
              reference.removeAttribute("title");
              return title;
            },
            theme: "tippy_popover",
            placement: "left",
            arrow: true,
            offset: [40, 0],
          });
        });
    },
  };
})(jQuery, window.Drupal, window.tippy);
