/**
 * @file
 */

(($, Drupal) => {
  Drupal.behaviors.vaGovDisplayServiceDescriptions = {
    attach(context) {
      const removeItems = () => {
        return context
          .querySelectorAll(".health_service_text_container")
          .forEach((item) => {
            item.remove();
          });
      };
      const descriptionFill = (ss) => {
        if (ss && ss.length > 0) {
          ss.forEach((service) => {
            service.addEventListener("change", () => {
              // Clear out any existing term content.
              removeItems();
              const div = context.createElement("div");
              div.className = "health_service_text_container";

              // Build up our term fields for display.
              if (drupalSettings.availableHealthServices[service.value].type) {
                const p1 = context.createElement("p");
                const s1 = context.createElement("strong");
                const typeString =
                  drupalSettings.availableHealthServices[service.value].type;
                p1.textContent = typeString;
                s1.textContent = "Type of care: ";
                div.appendChild(p1);
                p1.prepend(s1);
              }
              if (drupalSettings.availableHealthServices[service.value].name) {
                const p2 = context.createElement("p");
                const s2 = context.createElement("strong");
                p2.textContent =
                  drupalSettings.availableHealthServices[service.value].name;
                s2.textContent = "Patient friendly name: ";
                div.appendChild(p2);
                p2.prepend(s2);
              }
              if (
                drupalSettings.availableHealthServices[service.value].conditions
              ) {
                const p3 = context.createElement("p");
                const s3 = context.createElement("strong");
                p3.textContent =
                  drupalSettings.availableHealthServices[
                    service.value
                  ].conditions;
                s3.textContent = "Common conditions: ";
                div.appendChild(p3);
                p3.prepend(s3);
              }
              if (
                drupalSettings.availableHealthServices[service.value]
                  .description
              ) {
                const p4 = context.createElement("p");
                const s4 = context.createElement("strong");
                p4.textContent =
                  drupalSettings.availableHealthServices[
                    service.value
                  ].description;
                s4.textContent = "Service description: ";
                div.appendChild(p4);
                p4.prepend(s4);
              }

              // Plug in the term text below the select.
              service.after(div);
            });
          });
        }
      };
      // After services div is reloaded, operate on the selects.
      $(context).ajaxComplete(() => {
        const serviceSelects = context.querySelectorAll(
          ".field--name-field-service-name-and-descripti select"
        );
        descriptionFill(serviceSelects);
      });
    },
  };
})(jQuery, Drupal);
