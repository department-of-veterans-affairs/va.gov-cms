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
