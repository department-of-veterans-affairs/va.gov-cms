/**
 * @file
 */

(($, Drupal) => {
  Drupal.behaviors.vaGovEmailHelp = {
    attach() {
      // Adds a help email popup to Help menu link in admin toolbar.
      $(".toolbar-icon-help-main")
        .once("supportEmail")
        .click(() => {
          window.open(
            "mailto:vacmssupport@va.gov?subject=Support%20request:%20%5Badd%20topic%5D&body=Dear%20VACMS%20support%20team%2C%0A%5BThis%20is%20a%20template.%20%20You%20can%20delete%20the%20text%20you%20don%E2%80%99t%20need%2C%20and%20feel%20free%20to%20add%20your%20own.%5D%0A%0AI%20need%20help%20with%3A%20%0A-%20A%20bug%20%2F%20defect%20I%20encountered%0A-%20Something%20that%E2%80%99s%20not%20working%20as%20I%20expected%0A-%20Learning%20how%20to%20do%20something%20in%20the%20CMS%0A%0AHere%E2%80%99s%20some%20more%20detail%20about%20what%20I%20was%20trying%20to%20do%3A%0A%5BTell%20us%20more.%20It%20helps%20to%20add%20a%20step-by-step%20list%20of%20what%20we%E2%80%99d%20need%20to%20do%20to%20reproduce%20your%20problem.%5D%0A%0AI%20need%20a%20response%20by%3A%20%0A-%20Today%20-%20because%20the%20content%20needs%20to%20get%20published%20in%20the%20next%20deploy%0A-%20In%20the%20next%20few%20days%20-%20because%20I%20plan%20to%20publish%20this%20week%0A-%20Later%20on%20-%20I%E2%80%99m%20flexible%0A-%20This%20specific%20date%3A%20___%0A%0AHere%E2%80%99s%20some%20additional%20info%3A%20(Optional%2C%20but%20including%20these%20can%20help%20us%20understand%20and%20respond%20more%20quickly.)%0A-%20Attached%20screenshots%0A-%20Prod.cms.va.gov%20URL%0A-%20VA.gov%20URL%0A%0AThank%20you%2C%0A%0AYour%20name%0AYour%20title"
          );
          return false;
        });
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
      // Show the required style for paragraphs required via constraint logic.
      $("h4.label").each((item) => {
        const container = $(item).closest(
          ".field--type-entity-reference-revisions"
        );
        if (
          container &&
          container.attr("data-drupal-states").includes('"show-indicator":')
        ) {
          $(this).addClass("js-form-required form-required");
        }
      });
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
})(jQuery, window.Drupal);
