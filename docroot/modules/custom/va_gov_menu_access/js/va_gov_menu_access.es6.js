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
      const menuEnableCheckbox = document.getElementById("edit-menu-enable");
      const parentMenuSelect = document.querySelector(
        ".form-item-menu-menu-parent label"
      );
      if (parentMenuSelect) {
        parentMenuSelect.classList.add("form-required");
      }
      // Don't show menu settings widget to non admins on non-detail-pages.
      if (
        drupalSettings.vagov_menu_access.content_type ===
          "not-allowed-to-operate-on-menu" &&
        adminTest === false &&
        document.querySelector("details#edit-menu")
      ) {
        document.querySelector("details#edit-menu").style.display = "none";
      }

      function menuSelectHandler() {
        // If we don't have anything in the menu, don't show it.
        if (
          document.querySelector(".menu-parent-select") &&
          document
            .querySelector(".menu-parent-select")
            .classList.contains("no-available-menu-targets") &&
          document.getElementById("edit-menu")
        ) {
          document.getElementById("edit-menu").style.display = "none";
        }

        // When the menu link box is checked, fire logic.
        const parentOptions = context.querySelectorAll(
          "#edit-menu-menu-parent option"
        );

        if (
          document.getElementById("edit-menu-title") &&
          document.getElementById("edit-menu-title").hasAttribute("disabled") &&
          menuEnableCheckbox !== null
        ) {
          menuEnableCheckbox.disabled = true;
        }

        parentOptions.forEach((opt) => {
          // If an option doesn't pass the check, set it to disabled.
          if (opt && opt.text.includes(" | Disabled")) {
            opt.setAttribute("disabled", "");
            // Remove the disabled / no-link text - color change is visual cue.
            opt.innerHTML = opt.text
              .replace(/ \| Disabled/gi, "")
              .replace(/\(disabled\)/gi, "")
              .replace(/no-link/gi, "");
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
