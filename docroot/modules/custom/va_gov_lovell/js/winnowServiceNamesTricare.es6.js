/**
 * @file
 */

(($, Drupal) => {
  Drupal.behaviors.vaGovWinnowServiceNamesForTricare = {
    attach(context) {
      Drupal.isTricareSystem = (subcontext) => {
        // Lovell - grab VAMC System field (if it exists).
        let tricareSystem = false;
        const vamcSystemSelector = subcontext.getElementById(
          "edit-field-region-page"
        );
        if (vamcSystemSelector !== null) {
          // The entity ID for the Lovell - TRICARE subsystem.
          const tricareSystemId = "49011";
          if (vamcSystemSelector.value === tricareSystemId) {
            tricareSystem = true;
          }
        }
        return tricareSystem;
      };

      const winnowTricareServices = (options) => {
        if (options && options.length > 0) {
          const tricareSystem = Drupal.isTricareSystem(context);
          // Loop through all of the service options.
          // If TRICARE system - hide items with "vet".
          // If not TRICARE system - hide items with TRICARE.
          options.forEach((option) => {
            if (
              (!tricareSystem && option.text.includes("(TRICARE)")) ||
              (tricareSystem && option.text.toLowerCase().includes("vet"))
            ) {
              option.classList.add("hidden-option");
            } else {
              option.classList.remove("hidden-option");
            }
          });
        }
      };

      // If services are available on page load, operate on the selects.
      window.addEventListener("DOMContentLoaded", () => {
        // Add a change event listener to the VAMC System field.
        const systemSelect = context.getElementById("edit-field-region-page");
        if (systemSelect !== null) {
          systemSelect.addEventListener("change", () => {
            winnowTricareServices(
              context.querySelectorAll(
                ".field--name-field-service-name-and-descripti select option"
              )
            );
          });
          winnowTricareServices(
            context.querySelectorAll(
              ".field--name-field-service-name-and-descripti select option"
            )
          );
        }
      });
    },
  };
})(jQuery, Drupal);
