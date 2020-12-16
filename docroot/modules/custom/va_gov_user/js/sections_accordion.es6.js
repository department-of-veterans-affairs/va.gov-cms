/**
 * @file
 */

(($, Drupal) => {
  Drupal.behaviors.vaGovSectionsAccordion = {
    attach(context) {
      // Add aria-hidden attribute to all collapsed areas.
      $(".sections")
        .find(".subsections")
        .attr("aria-hidden", true)
        .addClass("hidden");

      $(".sections .toggle", context).on("click", (e) => {
        e.preventDefault();
        $(e.target).toggleClass("open");
        $(e.target).closest("li").find("a").toggleClass("open");
        $(e.target).attr("aria-pressed", (_, attr) => {
          return !(attr === "true");
        });
        $(e.target).attr("aria-expanded", (_, attr) => {
          return !(attr === "true");
        });
        $(e.target)
          .closest("li")
          .find(".subsections")
          .attr("aria-hidden", (_, attr) => {
            return !(attr === "true");
          });
        $(e.target).closest("li").find(".subsections").toggleClass("hidden");
      });
    },
  };
})(jQuery, window.Drupal);
