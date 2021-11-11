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
        // Grab the first selector that appears
        const serviceSelector = context.querySelector(
          ".field--name-field-service-name-and-descripti select"
        );
        const wysiwyg = context.getElementById("edit-field-body-wrapper");
        // Use the selection from first selector to determine whether or
        // not we show the taxonomy fields div and wysiwyg.
        let serviceSelectorSelectionClass = "empty-display-none";
        if (wysiwyg !== null) {
          wysiwyg.classList.add("empty-display-none");
        }
        if (
          serviceSelector !== undefined &&
          serviceSelector.options !== undefined &&
          serviceSelector.options[serviceSelector.selectedIndex].value !==
            "_none"
        ) {
          serviceSelectorSelectionClass = "not-empty-display-block";
          if (wysiwyg !== null) {
            wysiwyg.classList.remove("empty-display-none");
          }
        }
        // Build up our "?" button and tooltip text.
        const div = context.createElement("div");
        const button = context.createElement("button");
        button.className = "tooltip-toggle css-tooltip-toggle";
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
          drupalSettings.availableHealthServices[service.value].description !==
            ""
        ) {
          const p4 = context.createElement("p");
          const s4 = context.createElement("strong");
          p4.textContent = drupalSettings.availableHealthServices[
            service.value
          ].description.replace(/&nbsp;/g, " ");
          s4.textContent = "Service description: ";
          div.classList.remove("no-content");
          div.appendChild(p4);
          p4.prepend(s4);
        }
        // Plug in the term text below the select.
        service.after(div);
        // If we have contents, add a label above.
        if (div.textContent.length > 0) {
          const p = context.createElement("p");
          p.id = `${service.id}-services-general-description`;
          p.className = "services-general-description";
          p.textContent = "General service description";
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
      });
    },
  };
})(jQuery, Drupal);
