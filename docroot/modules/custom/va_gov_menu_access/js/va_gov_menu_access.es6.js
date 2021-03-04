/**
 * @file
 */

((Drupal, drupalSettings) => {
  /**
   * Behaviors for parent menu selector on node forms.
   * */
  Drupal.behaviors.parentMenuSelector = {
    attach(context) {
      const currentUserRoles =
        drupalSettings.vagov_menu_access.current_user_roles;
      const adminRoles = ["content_admin", "administrator"];
      const adminTest = adminRoles.some((role) =>
        currentUserRoles.includes(role)
      );

      function menuSelectHandler() {
        // When the menu link box is checked, fire logic.
        const parentOptions = context.querySelectorAll(
          "#edit-menu-menu-parent option"
        );
        if (
          document.getElementById("edit-menu-title").hasAttribute("disabled")
        ) {
          document.getElementById("edit-menu-enabled").disabled = true;
        }
        parentOptions.forEach((opt) => {
          // If an option doesn't pass the check, set it to disabled.
          if (opt && opt.text.includes(" | Disabled")) {
            opt.setAttribute("disabled", "");
            // Remove the disabled text - color change is visual cue.
            opt.innerHTML = opt.text
              .replace(/ \| Disabled/gi, "")
              .replace(/\(disabled\)/gi, "");
          }
        });
      }
      // If user is an admin, bypass restriction logic.
      if (!adminTest) {
        window.addEventListener("load", menuSelectHandler);
      }
    },
  };
})(Drupal, drupalSettings);
