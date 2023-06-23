/**
 * @file
 * Login form helpers.
 */

((Drupal) => {
  /**
   * Handles switching between PIV card & CMS user/pass login flows.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.loginFormToggle = {
    attach() {
      document
        .querySelector(".js-va-login-toggle")
        .addEventListener("click", (e) => {
          e.preventDefault();

          // Toggle class on form to control which inputs are shown.
          const loginForm = document.getElementById("user-login-form");
          // loginForm.toggleClass("piv-login form-login");
          loginForm.classList.toggle("piv-login");
          loginForm.classList.toggle("form-login");

          // Change text of toggle button based on which is shown.
          const loginToggle = document.getElementById("edit-toggle");

          if (loginToggle.value === "Developer log in") {
            loginToggle.value = "Log in with PIV";
          } else {
            loginToggle.value = "Developer log in";
          }

          // Move focus back to top of form when toggled.
          if (loginForm.classList.contains("piv-login")) {
            document.querySelector("a.samlauth-login-link").focus();
          } else {
            document.querySelector(".js-login-username input").focus();
          }
        });
    },
  };
})(Drupal);
