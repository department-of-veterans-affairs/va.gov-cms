/**
 * @file
 */

((Drupal) => {
  // Grab our fields and options.
  const adminField = document.getElementById("edit-field-administration");
  const vcFieldOptions = document.querySelectorAll("#edit-field-office option");
  const vcField = document.getElementById("edit-field-office");
  const winnower = () => {
    const pathType = drupalSettings.path.currentPath.split("/")[1];
    // Set our selects back to "Select a value." on add forms.
    if (
      typeof vcField !== "undefined" &&
      vcField !== null &&
      pathType === "add"
    ) {
      vcField.selectedIndex = "_none";
    }

    // Get our base match text string.
    const adminFieldText = adminField.options[adminField.selectedIndex].text;
    // Get our search string from the field text.
    const adminMatcher = adminFieldText.replace(/(^-+)/g, "");
    // Winnow vc field options that don't contain adminMatcher.
    vcFieldOptions.forEach((i) => {
      // Apply reset every time we fire.
      i.classList.remove("hidden-option");
      if (!i.text.includes(adminMatcher)) {
        i.classList.add("hidden-option");
      }
    });
  };

  Drupal.behaviors.vaGovLimitVcServiceOptions = {
    attach() {
      winnower();
      adminField.addEventListener("change", winnower);
    },
  };
})(Drupal);
