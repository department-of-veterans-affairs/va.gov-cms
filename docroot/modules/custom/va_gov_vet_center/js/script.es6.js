/**
 * @file
 */

(($, Drupal) => {
  /**
   * Behaviors for manipulating vast output on node forms.
   * */
  Drupal.behaviors.vetCenterVastDataNodeOutputManipulation = {
    attach(context) {
      // Check for email templates. If none present, bail.
      if (context.querySelectorAll(".admin-help-email-tpl").length) {
        const emailLinks = context.querySelectorAll(".admin-help-email-tpl");

        // Get the id from the node view output, or if on the form, the input.
        const facilityID = context.querySelector(
          ".field--name-field-facility-locator-api-id .field__item"
        )
          ? context.querySelector(
              ".field--name-field-facility-locator-api-id .field__item"
            ).textContent
          : context.querySelector("#edit-field-facility-locator-api-id-0-value")
              .value;

        // Last crumb will always be the title.
        const facilityName =
          context.querySelector(".breadcrumb li:last-child") !== null
            ? context
                .querySelector(".breadcrumb li:last-child")
                .textContent.trim()
            : "";

        // Loop through our tpls and replace vars with const's from above.
        emailLinks.forEach((emailLink) => {
          const eHref = emailLink.href;
          emailLink.setAttribute(
            "href",
            eHref
              // Two instances of the name, and this is more straightforward
              // than using the global flag.
              .replace("[js_entry_facility_name]", facilityName)
              .replace("[js_entry_facility_name]", facilityName)
              .replace("[js_entry_facility_id]", facilityID)
          );
        });

        const adminRoles = ["content_admin", "administrator"];
        const targetTypes = ["vet_center"];
        // If we are on a target type and user isn't admin, add a title,
        // and label to fieldgroup.
        if (
          drupalSettings.gtm_data.contentType &&
          targetTypes.some((item) =>
            drupalSettings.gtm_data.contentType.includes(item)
          ) &&
          !adminRoles.some((item) =>
            drupalSettings.gtm_data.userRoles.includes(item)
          )
        ) {
          // The tooltip div.
          const targetFieldGroup = context.querySelector(
            ".node__content > .not-editable.tooltip-layout"
          );

          // Create a wrapper for our tooltip legend and facility name.
          const facilityDataFieldGroup = context.createElement("div");
          // Fieldgroup legend
          const legend = context.createElement("h3");
          legend.style.fontFamily =
            "Lucida Grande, Lucida Sans Unicode, DejaVu Sans, Lucida Sans, sans-serif";
          legend.style.fontSize = "1rem";
          legend.innerHTML = "FACILITY DATA";
          // Facility name label.
          const label = context.createElement("div");
          label.classList.add("field__label");
          label.innerHTML = "Name of facility";
          // Facility name element.
          const fieldItem = context.createElement("div");
          const description = context.querySelector(
            "#locations-and-contact-information .tooltip-layout .description"
          );
          fieldItem.classList.add("field__item");
          fieldItem.innerHTML = facilityName;
          // Plug our name into the wrapper div.
          targetFieldGroup.insertBefore(fieldItem, targetFieldGroup.firstChild);
          // Plug our label into the wrapper div.
          targetFieldGroup.insertBefore(label, targetFieldGroup.firstChild);
          // Move our description below the legend.
          targetFieldGroup.insertBefore(
            description,
            targetFieldGroup.firstChild
          );
          // Plug our legend into the tootip div.
          targetFieldGroup.insertBefore(legend, targetFieldGroup.firstChild);
          // Plug our wrapper into the tooltip div.
          targetFieldGroup.appendChild(facilityDataFieldGroup);
          // Move our description above the Top of Page Legend.
          const topOfPage = context.querySelector(
            "#top-of-page-information .tooltip-layout"
          );
          const topOfPageHelp = context.getElementById("top-get-help-email");
          topOfPage.insertBefore(topOfPageHelp, topOfPage.firstChild);
        }
      }
    },
  };
})(jQuery, window.Drupal);
