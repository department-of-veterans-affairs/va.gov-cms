/**
 * @file
 */

(($, Drupal, Tippy) => {
  Drupal.behaviors.vaGovToolbar = {
    attach() {
      Tippy(".ajax-tippy", {
        content(reference) {
          reference.removeAttribute("title");
          return "Loading...";
        },
        flipOnUpdate: true,
      });
    },
  };
})(jQuery, window.Drupal, window.tippy);
