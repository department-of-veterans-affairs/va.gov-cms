/**
 * @file
 */

((Drupal) => {
  // We want to grab the container to watch it for changes.
  const servicesFieldset = document.getElementById(
    "inline-entity-form-field_health_services-form"
  );
  const winnower = (context) => {
    // Grab the selected values from the table.
    const selectedValues = context.querySelectorAll(
      "#inline-entity-form-field_health_services-form td.inline-entity-form-node-label"
    );
    // This is the new service select.
    const newServiceValueSelector = context.querySelectorAll(
      "#inline-entity-form-field_health_services-form .field--name-field-service-name-and-descripti select option"
    );
    // Plug the table values into a set for easy manipulation.
    const selectedValuesText = new Set();
    if (newServiceValueSelector) {
      selectedValues.forEach((i) => {
        // Grab the vc name for comparison.
        selectedValuesText.add(i.textContent);
      });

      newServiceValueSelector.forEach((i) => {
        // Sanity reset.
        i.classList.remove("hidden-option");
        // Check if the service is in the set.
        if (selectedValuesText.has(i.text)) {
          // Hide it so it can't be selected again.
          i.classList.add("hidden-option");
        }
      });
    }
  };

  Drupal.behaviors.vaGovPreventSelectDuplicates = {
    attach(context) {
      if (servicesFieldset) {
        // When drags, edits, deletions, or additions happen, fire the winnower.
        servicesFieldset.addEventListener("change", winnower(context));
      }
    },
  };
})(Drupal);
