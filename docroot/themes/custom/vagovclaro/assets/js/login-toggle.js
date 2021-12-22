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

        // Toggle class on form to control which inputs are shown.
        const loginForm = $('#user-login-form');
        loginForm.toggleClass('piv-login form-login');

        // Change text of toggle button based on which is shown.
        const loginToggle = $('#edit-toggle');
        console.log(loginToggle);
        loginToggle.prop('value', loginToggle.val() === "Login with password" ? "Login with PIV" : "Login with password");
      });
    },
  };
})(jQuery, Drupal);
