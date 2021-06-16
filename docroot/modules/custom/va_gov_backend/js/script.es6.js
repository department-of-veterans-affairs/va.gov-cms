/**
 * @file
 */

(($, Drupal) => {
  /**
   * Behaviors for 'details with image' field-group validation.
   * */
  Drupal.behaviors.fieldGroupDetailsWithImageValidation = {
    attach(context) {
      // Open any hidden parents.
      function fieldsetHandler() {
        $(this).attr("open", "");
      }
      // Engage field groups that use detailswithimage.
      $(".field-group-detailswithimage :input", context).each(
        function invalidInput() {
          const fieldGroupInput = $(this);
          this.addEventListener(
            "invalid",
            function c(e) {
              $(e.target).parents("details:not([open])").each(fieldsetHandler);
            },
            false
          );

          if (fieldGroupInput.hasClass("error")) {
            fieldGroupInput
              .parents("details:not([open])")
              .each(fieldsetHandler);
          }
        }
      );
    },
  };

  /**
   * Behaviors for manipulating vast output on node forms.
   * */
  Drupal.behaviors.vastDataNodeOutputManipulation = {
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
          : context.querySelector(
              ".form-item-field-facility-locator-api-id-0-value input"
            ).value;

        // Last crumb will always be the title.
        const facilityName = context
          .querySelector(".breadcrumb li:last-child")
          .textContent.trim();

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
        const targetTypes = ["health_care_local_facility", "vet_center"];
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
            "#locations-and-contact-information .description"
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

  Drupal.behaviors.vaGovClpLimitListOfLinks = {
    attach() {
      // Don't allow more than 3 link teasers in clp spotlight panel.
      if (
        $(
          "#field-clp-spotlight-link-teasers-add-more-wrapper .paragraphs-dropbutton-wrapper"
        ).length > 3
      ) {
        $(
          "#field-clp-spotlight-link-teasers-add-more-wrapper .field-add-more-submit.button--small.button"
        ).css("display", "none");
      }
    },
  };

  Drupal.behaviors.vaGovServiceLocationRemoveButton = {
    attach() {
      // Don't show remove button on first instance.
      const removeButtons = document.querySelectorAll(
        '.field--name-field-service-location .paragraphs-dropbutton-wrapper input[value="Remove"]'
      );

      if (removeButtons.length > 0) {
        removeButtons.forEach((button, i) => {
          if (i < 1) {
            button.style.display = "none";
          }
        });
      }
    },
  };

  Drupal.behaviors.vaGovAlertSingleComponent = {
    attach() {
      const reusableAlertRemovedIds = [];
      const reusableAlertAddedIds = [];
      const nonReusableAlertAddedIds = [];
      const nonReusableAlertSelectionIds = [];

      // Collects element id of reusable alert - place alert button.
      $(
        'input[id*="subform-field-alert-block-reference-entity-browser-entity-browser-open-modal"]'
      ).each((idx, element) => {
        reusableAlertRemovedIds.push($(element).attr("id"));
      });

      // Collects element id of a reusable alert entity reference.
      $('div[id*="field-alert-block-reference-current-items-0"]').each(
        (idx, element) => {
          reusableAlertAddedIds.push($(element).attr("id"));
        }
      );

      // Collects element id of a non reusable alert fieldset.
      $('fieldset[id*="subform-group-n"]').each((idx, element) => {
        nonReusableAlertAddedIds.push($(element).attr("id"));
      });

      // Loops through alerts that have place alert buttons and enables alert selection field.
      $.each(reusableAlertRemovedIds, (key, value) => {
        const y = $(`#${value}`)
          .parents(".paragraphs-subform")
          .children(".field--name-field-alert-selection")
          .find(".fieldset-wrapper")
          .children()
          .attr("id");
        $(`#${y}> div > input`).each((idx, element) => {
          $(element).prop("disabled", false);
        });
      });

      // Loops through alerts that have reusable alert entity references and disables alert selection field.
      $.each(reusableAlertAddedIds, (key, value) => {
        const x = $(`#${value}`)
          .parents(".paragraphs-subform")
          .children(".field--name-field-alert-selection")
          .find(".fieldset-wrapper")
          .children()
          .attr("id");

        $(`#${x}> div > input`).each((idx, element) => {
          $(element).prop("disabled", true);
        });
      });

      // Loops through alerts that have non reusable alert fieldsets present and disables alert selection field.
      $.each(nonReusableAlertAddedIds, (key, value) => {
        nonReusableAlertSelectionIds.push(
          $(`#${value}`)
            .closest(
              "div[id*='subform-field-alert-wrapper'],div[id*='alert-single-wrapper']"
            )
            .find(".paragraphs-subform")
            .first()
            .children(".field--name-field-alert-selection")
            .children()
            .children(".fieldset-wrapper")
            .children()
            .attr("id")
        );

        $.each(nonReusableAlertSelectionIds, (sectionKey, sectionValue) => {
          $(`#${sectionValue}> div > input`).each((idx, element) => {
            $(element).prop("disabled", true);
          });
        });
      });
    },
  };

  Drupal.behaviors.vaGovRequiredParagraphs = {
    attach() {
      // Snowflake cases for entity browsers. And classic paragraphs.
      $("details#edit-field-clp-resources summary").addClass(
        "js-form-required form-required"
      );
      $("details#edit-field-clp-events-references summary").addClass(
        "js-form-required form-required"
      );
      $(
        "details#edit-group-video .field--type-entity-reference.field--name-field-media span.fieldset-legend"
      ).addClass("js-form-required form-required");
      $("#edit-field-clp-stories-teasers-wrapper").attr({
        required: "required",
        "aria-required": "true",
      });
      $("#edit-field-clp-stories-teasers-wrapper strong").addClass(
        "form-required"
      );
    },
  };

  Drupal.behaviors.vaGovStandaloneQABehaviors = {
    attach() {
      // Make sure we trigger the link paragraph to appear when
      // empty form is on page.
      const addMoreLinks = document.getElementById(
        "field-related-information-link-teaser-add-more"
      );
      const linkCount = document.querySelectorAll(
        ".paragraph-type--link-teaser"
      ).length;
      if (addMoreLinks && linkCount < 1) {
        addMoreLinks.dispatchEvent(new MouseEvent("click"));
      }
    },
  };
})(jQuery, window.Drupal);
