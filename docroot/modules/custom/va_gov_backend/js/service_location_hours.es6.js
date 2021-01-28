/**
 * @file
 */

((Drupal) => {
  const displayHours = (toggle, table) => {
    if (toggle.value === "2") {
      table.style.display = "block";
    } else {
      table.style.display = "none";
    }
  };

  Drupal.behaviors.vaGovServiceLocationHours = {
    attach() {
      // Grab our hour selects.
      const hourSelects = document.querySelectorAll(
        ".field--name-field-hours select"
      );
      hourSelects.forEach((hourSelect) => {
        // Grab our closest hours table.
        const hours = hourSelect.parentElement.parentElement.nextElementSibling;

        window.addEventListener("load", () => {
          // Determine whether or not to display on load.
          displayHours(hourSelect, hours);
        });

        hourSelect.addEventListener("change", () => {
          // Determine whether or not to display after selection.
          displayHours(hourSelect, hours);
        });
      });
    },
  };
})(Drupal);
