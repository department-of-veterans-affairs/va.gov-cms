/**
 * @file
 */

((Drupal) => {
  // Grab our fields.
  const adminField = document.getElementById("edit-field-administration");

  const checkBoxDivs = document.querySelectorAll(
    "#edit-field-banner-alert-vamcs-wrapper div.form-type-checkbox"
  );

  const winnower = () => {
    // Get our list of nids user can't access.
    const disallowedValues = drupalSettings.va_gov_vamc.disallowed_vamc_options;

    // Disable our disallowed options.
    checkBoxDivs.forEach((i) => {
      const currentOptionValue = parseInt(i.querySelector("input").value, 10);
      if (disallowedValues.includes(currentOptionValue)) {
        if (i.querySelector("input").checked) {
          i.querySelector("input").style.opacity = ".6";
          i.querySelector("input").style.pointerEvents = "none";
        } else {
          i.querySelector("input").setAttribute("disabled", "disabled");
        }
      }
    });
  };

  Drupal.behaviors.vaGovLimitServiceOptions = {
    attach() {
      const currentUserRoles =
        drupalSettings.vagov_menu_access.current_user_roles;
      const adminRoles = ["content_admin", "administrator"];
      const adminTest = adminRoles.some((role) =>
        currentUserRoles.includes(role)
      );
      if (!adminTest) {
        winnower();
        adminField.addEventListener("change", winnower);
      }
    },
  };
})(Drupal);
