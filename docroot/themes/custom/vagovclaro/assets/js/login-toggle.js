/**
 * @file
 * Login form helpers.
 */

(($, Drupal) => {
  /**
   * Handles switching between PIV card & CMS user/pass login flows.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.loginFormToggle = {
    attach() {
      $('.js-va-login-toggle').click((event) => {
        event.preventDefault();
        console.log('i\'m attached');

        const PIV = $('#edit-simplesamlphp-auth-login-link');
        // const userPass = $('');

        // const target = $(event.target).attr("href");
        // const scrollToPosition =
        //   $(target).offset().top - (Drupal.getAdminToolbarHeight() + 10);
        //
        // $("html").animate({ scrollTop: scrollToPosition }, 500, () => {
        //   window.location.hash = `${target}`;
        //   $("html").animate({ scrollTop: scrollToPosition }, 0);
        // });
      });
    },
  };
})(jQuery, Drupal);
