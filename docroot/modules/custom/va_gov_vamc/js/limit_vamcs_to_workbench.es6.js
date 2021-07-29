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
    // Get our base match text string.
    const adminFieldText = adminField.options[adminField.selectedIndex].text;
    // Get our search string from the field text.
    const adminMatcher = adminFieldText.replace(/(^-+)/g, "");

    // Winnow options that don't contain adminMatcher.
    checkBoxDivs.forEach((i) => {
      // Apply reset everytime we fire.
      const text = i.querySelector("label span.field-content").textContent;
      i.classList.remove("hidden-option");
      if (!text.includes(adminMatcher)) {
        i.classList.add("hidden-option");
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
