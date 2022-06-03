/**
 * @file
 */

((Drupal) => {
  Drupal.behaviors.vaGovFacilityStatusDetails = {
    attach() {
      // Hide/show and require facility status details based on status value.
      function detailsRequired(action) {
        const detailswrapper = document.querySelector(
          ".field--name-field-operating-status-more-info"
        );
        const label = detailswrapper.querySelector(".form-item label");
        const details = detailswrapper.querySelector(".form-item textarea");
        if (action === "yes") {
          detailswrapper.style.display = "block";
          label.classList.add("js-form-required", "form-required");
          details.classList.add("required");
          details.setAttribute("required", "required");
        } else {
          detailswrapper.style.display = "none";
          label.classList.remove("js-form-required", "form-required");
          details.classList.remove("required");
          details.removeAttribute("required");
        }
      }

      // Grab the operating status radio buttons.
      const statusradios = document.querySelectorAll(
        ".field--name-field-operating-status-facility .form-radio"
      );
      statusradios.forEach((radio) => {
        // Set initial visibility for status details based on status value.
        if (radio.checked) {
          if (radio.value === "normal") {
            detailsRequired("no");
          } else {
            detailsRequired("yes");
          }
        }
        radio.addEventListener("click", () => {
          // Detemine whether or not to display after radio interaction.
          if (radio.checked) {
            if (radio.value === "normal") {
              detailsRequired("no");
            } else {
              detailsRequired("yes");
            }
          }
        });
      });
    },
  };
})(Drupal);
