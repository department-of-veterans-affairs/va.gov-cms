/**
 * @file
 */

(($, Drupal) => {
  Drupal.behaviors.vaGovServiceLocationAddress = {
    attach(context) {
      // Add or remove the classes and attributes needed to make the address required.
      function addressRequired(address, action) {
        address
          .find(".form-item label")
          .not(".visually-hidden")
          .each(function cycleAddress() {
            if (action === "yes") {
              $(this).addClass("js-form-required form-required");
              $(this)
                .next("input, select")
                .addClass("required")
                .attr("required", "required");
            } else {
              $(this).removeClass("js-form-required form-required");
              $(this)
                .next("input, select")
                .removeClass("required")
                .removeAttr("required");
            }
          });
      }

      $(".paragraph-type--service-location-address .form-checkbox").each(
        function cycleCheckbox() {
          // Grab our closest address.
          const $address = $(this)
            .parent()
            .parent()
            .next(".field--type-address");
          // Set initial visibility for the address based on the checkbox value.
          if ($(this).prop("checked")) {
            $address.css("display", "none");
            addressRequired($address, "no");
          } else {
            $address.css("display", "block");
            addressRequired($address, "yes");
          }
          // Determine whether or not to display after checkbox interaction.
          $(this).on("change", function onChange() {
            if ($(this).prop("checked")) {
              $address.css("display", "none");
              addressRequired($address, "no");
            } else {
              $address.css("display", "block");
              addressRequired($address, "yes");
            }
          });
        }
      );

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
})(jQuery, Drupal);
