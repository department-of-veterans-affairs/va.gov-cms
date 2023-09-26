/**
 * @file
 */

(($, Drupal) => {
  Drupal.behaviors.vaGovDisplayServiceDescriptions = {
    attach(context) {
      const loadItems = (service) => {
        // Clear out any existing term content.
        if (
          context.getElementById(`${service.id}-health_service_text_container`)
        ) {
          context
            .getElementById(`${service.id}-health_service_text_container`)
            .remove();
        }
        if (
          context.getElementById(`${service.id}-services-general-description`)
        ) {
          context
            .getElementById(`${service.id}-services-general-description`)
            .remove();
        }
        // Check if system is Lovell - TRICARE.
        const tricareSystem = Drupal.isTricareSystem(context);
        // Grab the first selector that appears
        const serviceSelector = context.querySelector(
          ".field--name-field-service-name-and-descripti select"
        );
        // Use the selection from first selector to determine whether or
        // not we show the taxonomy fields div.
        let serviceSelectorSelectionClass = "empty-display-none";
        if (
          serviceSelector !== undefined &&
          serviceSelector.options !== undefined &&
          serviceSelector.options[serviceSelector.selectedIndex].value !==
            "_none"
        ) {
          serviceSelectorSelectionClass = "not-empty-display-block";
        }
        // Build up our "?" button and tooltip text.
        const div = context.createElement("div");
        const button = context.createElement("button");
        button.className = "tooltip-toggle css-tooltip-toggle";
        button.value = `Why can't I edit this? National editors keep these descriptions standardized to help Veterans identify the services they need.`;
        button.type = "button";
        // Add css formatting from "tippy" css library.
        button.ariaLabel = "tooltip";
        button.setAttribute(
          "data-tippy",
          `Why can't I edit this?\nNational editors keep these descriptions standardized to help Veterans identify the services they need.`
        );
        button.setAttribute("data-tippy-pos", "right");
        button.setAttribute("data-tippy-animate", "fade");
        button.setAttribute("data-tippy-size", "large");
        button.id = "service-tooltip css-tooltip";
        div.id = `${service.id}-health_service_text_container`;
        div.className = `no-content health_service_text_container field-group-tooltip tooltip-layout centralized css-tooltip ${serviceSelectorSelectionClass}`;
        // Smash it all together.
        div.appendChild(button);

        // Build up our term fields for display.
        if (
          drupalSettings.availableHealthServices[service.value] !== undefined &&
          drupalSettings.availableHealthServices[service.value].type !== ""
        ) {
          const p1 = context.createElement("p");
          const s1 = context.createElement("strong");
          const typeString =
            drupalSettings.availableHealthServices[service.value].type;
          p1.textContent = typeString;
          s1.textContent = "Type of care: ";
          div.classList.remove("no-content");
          div.appendChild(p1);
          p1.prepend(s1);
        }
        if (
          drupalSettings.availableHealthServices[service.value] !== undefined &&
          drupalSettings.availableHealthServices[service.value].name !== ""
        ) {
          const p2 = context.createElement("p");
          const s2 = context.createElement("strong");
          p2.textContent =
            drupalSettings.availableHealthServices[service.value].name;
          s2.textContent = "Patient friendly name: ";
          div.classList.remove("no-content");
          div.appendChild(p2);
          p2.prepend(s2);
        }
        if (
          drupalSettings.availableHealthServices[service.value] !== undefined &&
          drupalSettings.availableHealthServices[service.value].conditions !==
            ""
        ) {
          const p3 = context.createElement("p");
          const s3 = context.createElement("strong");
          p3.textContent = drupalSettings.availableHealthServices[
            service.value
          ].conditions.replace(/&nbsp;/g, " ");
          s3.textContent = "Common conditions: ";
          div.classList.remove("no-content");
          div.appendChild(p3);
          p3.prepend(s3);
        }
        if (
          drupalSettings.availableHealthServices[service.value] !== undefined &&
          (drupalSettings.availableHealthServices[service.value].description !==
            "" ||
            drupalSettings.availableHealthServices[service.value]
              .tricare_description !== "")
        ) {
          const p4 = context.createElement("p");
          const s4 = context.createElement("strong");
          // If system is Lovell - TRICARE and service has TRICARE description.
          if (
            tricareSystem === true &&
            drupalSettings.availableHealthServices[service.value]
              .tricare_description !== ""
          ) {
            // Display the TRICARE service description for this service.
            p4.textContent = drupalSettings.availableHealthServices[
              service.value
            ].tricare_description.replace(/&nbsp;/g, " ");
          }
          if (
            !p4.textContent &&
            drupalSettings.availableHealthServices[service.value]
              .description !== ""
          ) {
            // If no TRICARE service description was provided, use the default.
            p4.textContent = drupalSettings.availableHealthServices[
              service.value
            ].description.replace(/&nbsp;/g, " ");
          }
          if (p4.textContent) {
            s4.textContent = `${
              drupalSettings.availableHealthServices[service.value]
                .vc_vocabulary_service_description_label
            }: `;
            div.classList.remove("no-content");
            div.appendChild(p4);
            p4.prepend(s4);
          }
        }
        // Plug in the term text below the select.
        service.after(div);
        // If we have contents, add a label above.
        if (div.textContent.length > 0) {
          const p = context.createElement("p");
          const d = context.createElement("div");
          p.id = `${service.id}-services-general-description`;
          p.className = "services-general-description";
          p.textContent = "General service description";
          d.className = "description ief-service-type";
          // Adding in help text for general description
          d.textContent =
            drupalSettings.availableHealthServices[
              service.value
            ].vc_vocabulary_description_help_text;
          p.appendChild(d);
          service.after(p);
        }
      };

      const descriptionFill = (ss) => {
        if (ss && ss.length > 0) {
          // Loop through all of the terms and see if they match the selection.
          ss.forEach((service) => {
            loadItems(service);
            service.addEventListener("change", () => {
              loadItems(service);
            });
          });
        }
      };

      // After services div is reloaded, operate on the selects.
      // Have to use the old school jQuery method here, because Promises
      // & Fetch libraries both require an url.
      $(context).ajaxComplete(() => {
        descriptionFill(
          context.querySelectorAll(
            ".field--name-field-service-name-and-descripti select"
          )
        );
      });

      // If services are available on page load, operate on the selects.
      window.addEventListener("DOMContentLoaded", () => {
        descriptionFill(
          context.querySelectorAll(
            ".field--name-field-service-name-and-descripti select"
          )
        );
        // Add a change event listener to the VAMC System field.
        const systemSelect = context.getElementById("edit-field-region-page");
        if (systemSelect !== null) {
          systemSelect.addEventListener("change", () => {
            descriptionFill(
              context.querySelectorAll(
                ".field--name-field-service-name-and-descripti select"
              )
            );
          });
        }
      });
    },
  };
})(jQuery, Drupal);
