/**
 * @file
 */

((Drupal) => {
  Drupal.behaviors.vaGovServiceLocationAddress = {
    attach(context) {
      // Add or remove the classes and attributes needed to make the address required.
      function addressRequired(address, action) {
        const labels = address.querySelectorAll(".form-item label");
        labels.forEach((label) => {
          if (!label.classList.contains("visually-hidden")) {
            if (action === "yes") {
              label.classList.add("js-form-required", "form-required");
              label.nextElementSibling.classList.add("required");
              label.nextElementSibling.setAttribute("required", "required");
            } else {
              label.classList.remove("js-form-required", "form-required");
              label.nextElementSibling.classList.remove("required");
              label.nextElementSibling.removeAttribute("required");
            }
          }
        });
      }

      // Grab our address toggles.
      const checkboxes = document.querySelectorAll(
        ".paragraph-type--service-location-address .form-checkbox"
      );
      checkboxes.forEach((check) => {
        // Grab our closest address.
        const address = check.parentElement.parentElement.nextElementSibling;
        // Set initial visibility for the address based on the checkbox value.
        if (check.checked) {
          address.style.display = "none";
          addressRequired(address, "no");
        } else {
          address.style.display = "block";
          addressRequired(address, "yes");
        }
        check.addEventListener("click", () => {
          // Detemine whether or not to display after checkbox interaction.
          if (check.checked) {
            address.style.display = "none";
            addressRequired(address, "no");
          } else {
            address.style.display = "block";
            addressRequired(address, "yes");
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
