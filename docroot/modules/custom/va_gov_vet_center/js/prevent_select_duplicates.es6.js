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
        // Add the name & requirement as an aria to parent tr for sorting later.
        const nameSortString = `${i.nextElementSibling.textContent
          .replace("Optional", "z")
          .replace(/ /gi, "-")
          .toLowerCase()}-${i.textContent.toLowerCase()}`;
        i.parentElement.setAttribute("aria-vc-name", nameSortString);
        // Add the optional/required status to parent tr for further sorting.
        i.parentElement.setAttribute(
          "aria-required-status",
          i.nextElementSibling.textContent
        );
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

  const sortByAriaVcName = (a, b) => {
    if (a.getAttribute("aria-vc-name") < b.getAttribute("aria-vc-name"))
      return -1;
    if (a.getAttribute("aria-vc-name") > b.getAttribute("aria-vc-name"))
      return 1;
    return 0;
  };

  const alphaSortRows = (context) => {
    const tableRowsArray = Array.from(
      context.querySelectorAll("tr[aria-vc-name]")
    );

    const sorted = tableRowsArray.sort(sortByAriaVcName);
    const servicesTable = context.querySelector(
      "#inline-entity-form-field_health_services-form table tbody"
    );

    if (servicesTable !== null) {
      sorted.forEach((e) => {
        e.parentElement.appendChild(e);
      });
    }
  };

  Drupal.behaviors.vaGovPreventSelectDuplicates = {
    attach(context) {
      if (servicesFieldset) {
        // When drags, edits, deletions, or additions happen, fire the winnower.
        servicesFieldset.addEventListener("change", winnower(context));
        // Run the alpha sort.
        context.addEventListener("load", alphaSortRows(context));
      }
    },
  };
})(Drupal);
