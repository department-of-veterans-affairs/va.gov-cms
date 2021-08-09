/**
 * @file
 * Anchor link behaviors.
 */

(($, Drupal) => {
  /**
   * Return the combined height of the admin toolbar & tray.
   *
   * @return {number}
   *   Height in pixels.
   */
  Drupal.getAdminToolbarHeight = () => {
    const toolbarHeight = $("#toolbar-bar").height() || 0;
    const tooltrayHeight =
      $("#toolbar-item-administration-tray.toolbar-tray-horizontal").height() ||
      0;
    return toolbarHeight + tooltrayHeight;
  };

  /**
   * Attaches anchor link behavior to links.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.vagovadminAnchorLinks = {
    attach() {
      const tabs = document.querySelectorAll("a.vertical-tabs__menu-link");
      tabs.forEach((tab) => {
        $(tab).on("click", (event) => {
          event.preventDefault();

          const { target } = event;
          const targetHref = target.getAttribute("href");
          const scrollToPosition =
            $(target).offset().top - (Drupal.getAdminToolbarHeight() + 10);

          $("html").animate({ scrollTop: scrollToPosition }, 500, () => {
            window.location.hash = `${targetHref}`;
            $("html").animate({ scrollTop: scrollToPosition }, 0);
          });
        });
      });
    },
  };
})(jQuery, Drupal);
