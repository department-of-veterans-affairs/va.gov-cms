/**
 * @file
 */

(($, Drupal) => {
  Drupal.behaviors.vaGovSetServiceSelects = {
    attach(context) {
      const adminRoles = ["content_admin", "administrator"];

      // After services div is reloaded, operate on the selects.
      $(context).ajaxComplete(() => {
        // The name of the vc to plugin to the select option value.
        const vcSection = context.getElementById("edit-field-administration");
        // Admin field uses the dashes.
        const vcSectionValue = vcSection.options[vcSection.selectedIndex].text;
        // Office field doesn't, so remove them.
        const vcSectionValueCleaned = vcSectionValue
          .replace(/^(---)/, "")
          .replace(/^(--)/, "")
          .replace(/^(-)/, "");
        // Grab the office selector.
        const offices = context.querySelector(
          ".field--type-entity-reference.field--name-field-office select"
        );
        // Grab the admin selector.
        const admins = context.querySelector(
          ".field--type-entity-reference.field--name-field-administration select"
        );
        const allOffices = context.querySelectorAll(
          ".field--type-entity-reference.field--name-field-health-services .field--type-entity-reference.field--name-field-office"
        );
        const allAdmins = context.querySelectorAll(
          ".field--type-entity-reference.field--name-field-health-services .field--type-entity-reference.field--name-field-administration"
        );
        // If user isn't admin, hide the selects.
        if (
          adminRoles.some((item) =>
            drupalSettings.gtm_data.userRoles.includes(item)
          ) &&
          allOffices &&
          allAdmins
        ) {
          allOffices.forEach((office) => {
            office.style.display = "none";
          });
          allAdmins.forEach((admin) => {
            admin.style.display = "none";
          });
        }
        // Set the values to match.
        if (offices) {
          offices.selectedIndex = [...offices.options].findIndex(
            (option) => option.text === vcSectionValueCleaned
          );
        }
        if (admins) {
          admins.selectedIndex = [...admins.options].findIndex(
            (option) => option.text === vcSectionValue
          );
        }
      });
    },
  };
})(jQuery, Drupal);
