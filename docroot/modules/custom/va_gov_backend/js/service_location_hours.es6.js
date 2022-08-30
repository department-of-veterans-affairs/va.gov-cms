/**
 * @file
 */

((Drupal) => {
  const displayHours = (value, toggle, table) => {
    if (toggle.checked) {
      if (toggle.value === value) {
        table.style.display = "block";
      } else {
        table.style.display = "none";
      }
    }
  };

  Drupal.behaviors.vaGovServiceLocationHours = {
    attach() {
      // Grab our hour selects.
      const hourSelects = document.querySelectorAll(
        ".field--name-field-hours input"
      );
      hourSelects.forEach((hourSelect) => {
        // Grab our closest hours table.
        const hours = hourSelect.parentElement.parentElement.parentElement.parentElement.parentElement.nextElementSibling;
        const facilityHours = hourSelect.parentElement.parentElement.parentElement.parentElement.nextElementSibling;

        window.addEventListener("load", () => {
          // Determine whether or not to display on load.
          displayHours("2", hourSelect, hours);
          displayHours("0", hourSelect, facilityHours);
        });

        hourSelect.addEventListener("change", () => {
          // Determine whether or not to display after selection.
          displayHours("2", hourSelect, hours);
          displayHours("0", hourSelect, facilityHours);
        });
      });
    },
  };
})(Drupal);
