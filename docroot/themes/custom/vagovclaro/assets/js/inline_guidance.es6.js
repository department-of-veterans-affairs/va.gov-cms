/**
 * @file
 * inline_guidance behaviors.
 */

(($, Drupal) => {
  /**
   * Attaches inline guidance behaviors.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.vaGovInlineGuidance = {
    attach: () => {
      $("#inline-guidance-trigger")
        .once()
        .click((e) => {
          e.preventDefault();
          if ($("#inline-guidance-text-box").hasClass("hide")) {
            $("#inline-guidance-text-box").removeClass("hide");
            $("#inline-guidance-text-box").addClass("show");
            $("#inline-guidance-trigger").attr("aria-expanded", "true");
            setTimeout(() => {
              $("#inline-guidance-trigger").focus();
            }, 800);
          } else {
            $("#inline-guidance-text-box").removeClass("show");
            $("#inline-guidance-text-box").addClass("hide");
            $("#inline-guidance-trigger").attr("aria-expanded", "false");
            setTimeout(() => {
              $("#inline-guidance-trigger").focus();
            }, 500);
          }
        });
    },
  };
})(jQuery, Drupal);
