/**
 * @file
 */

((Drupal) => {
  // Grab our fields and options.
  const adminField = document.getElementById("edit-field-administration");
  const facilityFieldOptions = document.querySelectorAll(
    "#edit-field-facility-location option"
  );
  const systemFieldOptions = document.querySelectorAll(
    "#edit-field-regional-health-service option"
  );
  const facilityField = document.getElementById("edit-field-facility-location");
  const systemField = document.getElementById(
    "edit-field-regional-health-service"
  );
  const winnower = () => {
    // Set our selects back to "Select a value."
    if (typeof facilityField !== "undefined" && facilityField !== null) {
      facilityField.selectedIndex = "_none";
    }
    if (typeof systemField !== "undefined" && systemField !== null) {
      systemField.selectedIndex = "_none";
    }

    // Get our base match text string.
    const adminFieldText = adminField.options[adminField.selectedIndex].text;
    // Get our search string from the field text.
    const adminMatcher = adminFieldText.replace(/(^-+)/g, "");
    // Winnow facility field options that don't contain adminMatcher.
    facilityFieldOptions.forEach((i) => {
      // Apply reset everytime we fire.
      i.classList.remove("hidden-option");
      if (!i.text.includes(adminMatcher)) {
        i.classList.add("hidden-option");
      }
    });
    // Winnow system field options that don't contain adminMatcher.
    systemFieldOptions.forEach((i) => {
      // Apply reset every time we fire.
      i.classList.remove("hidden-option");
      if (!i.text.includes(adminMatcher)) {
        i.classList.add("hidden-option");
      }
    });
  };

  Drupal.behaviors.vaGovLimitServiceOptions = {
    attach() {
      winnower();
      adminField.addEventListener("change", winnower);
    },
  };
})(Drupal);
