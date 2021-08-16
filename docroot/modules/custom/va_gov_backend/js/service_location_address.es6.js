/**
 * @file
 */

((Drupal) => {
  Drupal.behaviors.vaGovServiceLocationAddress = {
    attach(context) {
      // Grab our address toggles.
      const checkboxes = document.querySelectorAll(
        ".paragraph-type--service-location-address .form-checkbox"
      );
      checkboxes.forEach((check) => {
        // Grab our closest address.
        const address = check.parentElement.parentElement.nextElementSibling;
        check.addEventListener("click", () => {
          // Detemine whether or not to display after checkbox interaction.
          if (check.checked) {
            address.style.display = "none";
          } else {
            address.style.display = "block";
          }
        });
      });

      const serviceLocations = context.querySelectorAll(
        ".paragraph-type--service-location"
      );
      const serviceLocationsToggles = context.querySelectorAll(
        ".paragraph-type--service-location .paragraphs-dropdown-toggle"
      );
      // If only one location, remove the delete button.
      if (serviceLocations.length < 2) {
        serviceLocationsToggles.forEach((item) => {
          item.style.display = "none";
        });
      }
    },
  };
})(Drupal);
