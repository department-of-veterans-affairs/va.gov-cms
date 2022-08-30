/**
 * @file
 */

(($, Drupal) => {
  const displayHours = (value, toggle, table) => {
    if (toggle.checked) {
      if (toggle.value === value) {
        table.style.display = "block";
        if (value = "0") {
          $(table).once("button-build").each(function() {
            const button = document.createElement("button");
            button.className = "tooltip-toggle";
            button.value =
              "Why can't I edit this? VHA keeps these descriptions standardized to help Veterans identify the services they need.";
            button.type = "button";
            // Add css formatting from "tippy" css library.
            button.ariaLabel = "tooltip";
            button.setAttribute(
              "data-tippy",
              "Why can't I edit this?\nVHA keeps these descriptions standardized to help Veterans identify the services they need."
            );
            button.setAttribute("data-tippy-pos", "right");
            button.setAttribute("data-tippy-animate", "fade");
            button.setAttribute("data-tippy-size", "large");
            table.className = `no-content health_service_text_container field-group-tooltip tooltip-layout centralized css-tooltip`;
            // Smash it all together.
            table.appendChild(button);
          });

        }
      } else {
        table.style.display = "none";
      }
    }
  };

  Drupal.behaviors.vaGovServiceLocationHours = {
    attach(context) {
      // Grab our hour selects.
      const hourSelects = document.querySelectorAll(
        ".field--name-field-hours input"
      );
      hourSelects.forEach((hourSelect) => {
        // Grab our closest hours table.
        const hours =
                hourSelect.parentElement.parentElement.parentElement.parentElement
                .parentElement.nextElementSibling;
        const facilityHours =
                hourSelect.parentElement.parentElement.parentElement.parentElement
                .nextElementSibling;
        // Build up our "?" button and tooltip text.



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
})(jQuery, Drupal);
