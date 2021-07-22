/**
 * @file
 */

(($, Drupal) => {
  Drupal.behaviors.vaGovSetServiceSelects = {
    attach(context) {
      const adminRoles = ["content_admin", "administrator"];

      // After services div is reloaded, operate on the selects.
      $(context).ajaxComplete(() => {
        // The name of the vamc to plugin to the select option value.
        const vamcSection = context.getElementById("edit-field-administration");
        // Admin field uses the dashes.
        const vamcSectionValue =
          vamcSection.options[vamcSection.selectedIndex].text;

        // Grab the admin selector.
        const admins = context.querySelector(
          ".field--type-entity-reference.field--name-field-administration select"
        );
        const allAdmins = context.querySelectorAll(
          ".field--name-field-banner-alert .field--type-entity-reference.field--name-field-administration.field--widget-options-select"
        );
        // If user isn't admin, hide the selects.
        if (
          !adminRoles.some((item) =>
            drupalSettings.gtm_data.userRoles.includes(item)
          ) &&
          allAdmins
        ) {
          allAdmins.forEach((admin) => {
            admin.style.display = "none";
          });
        }
        // Set the values to match.
        if (admins) {
          admins.selectedIndex = [...admins.options].findIndex(
            (option) => option.text === vamcSectionValue
          );
        }
      });
    },
  };
})(jQuery, Drupal);
