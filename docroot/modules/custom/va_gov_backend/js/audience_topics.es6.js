/**
 * @file
 */

(($, Drupal) => {
  /**
   * Ensure that a maximum of 4 tags + audiences may be selected.
   */
  function enforceMaximumNumberOfTags() {
    // Get the total number of tags selected in the Topics fields.
    let total = $('div[id^="edit-field-tags-0-subform-field-topics"]').find(
      "input:checked"
    ).length;

    // Prevent showing the Beneficiaries field if we already have 4 topic tags.
    if (total >= 4) {
      $(
        'select[id^="edit-field-tags-0-subform-field-audience-selection"]'
      ).attr("disabled", true);
      $("div.form-item-field-tags-0-subform-field-audience-selection").addClass(
        "form-disabled"
      );

      $(
        'div[id^="edit-field-tags-0-subform-field-audience-beneficiares-none"]'
      ).attr("checked", true);
      $(
        'div[id^="edit-field-tags-0-subform-field-non-beneficiares-none"]'
      ).attr("checked", true);

      $(
        'div[id^="edit-field-tags-0-subform-field-non-beneficiares-wrapper"]'
      ).hide();
      $(
        'div[id^="edit-field-tags-0-subform-field-audience-beneficiares-wrapper"]'
      ).hide();
      $('select[id^="edit-field-tags-0-subform-field-audience-selection"]').val(
        "_none"
      );
    } else {
      $(
        'select[id^="edit-field-tags-0-subform-field-audience-selection"]'
      ).attr("disabled", false);
      $(
        "div.form-item-field-tags-0-subform-field-audience-selection"
      ).removeClass("form-disabled");
    }
    // Find out if there is a Beneficiary/Non-beneficiary term selected and increase the total if so.
    let audienceSelected = $(
      'div[id^="edit-field-tags-0-subform-field-audience-beneficiares-wrapper"]'
    ).find(
      'input:not([id^="edit-field-tags-0-subform-field-audience-beneficiares-none"]):checked'
    ).length;
    if (
      $(
        'select[id^="edit-field-tags-0-subform-field-audience-selection"]'
      ).val() === "non-beneficiaries"
    ) {
      audienceSelected = $(
        'div[id^="edit-field-tags-0-subform-field-non-beneficiares-wrapper"]'
      ).find(
        'input:not([id^="edit-field-tags-0-subform-field-non-beneficiares-none"]):checked'
      ).length;
    }

    total += audienceSelected;

    if (total >= 4) {
      // If a total of four or more tags have been selected, prevent the user from selecting more.
      $('div[id^="edit-field-tags-0-subform-field-topics"]')
        .find("input[type=checkbox]:not(:checked)")
        .attr("disabled", true);
    } else {
      // Otherwise, ensure that more tags may be selected.
      $('div[id^="edit-field-tags-0-subform-field-topics"]')
        .find("input[type=checkbox]")
        .attr("disabled", false);
    }
  }

  Drupal.behaviors.vaGovAudienceTopics = {
    attach() {
      // Normalize select and taxonomy display on page load.
      if (
        $(
          'div[id^="edit-field-tags-0-subform-field-non-beneficiares-wrapper"]'
        ).find(
          'input:not([id^="edit-field-tags-0-subform-field-non-beneficiares-none"]):checked'
        ).length
      ) {
        $(
          'select[id^="edit-field-tags-0-subform-field-audience-selection"]'
        ).val("non-beneficiaries");
        $(
          'div[id^="edit-field-tags-0-subform-field-non-beneficiares-wrapper"]'
        ).show();
        $(
          'div[id^="edit-field-tags-0-subform-field-audience-beneficiares-wrapper"]'
        ).hide();
      } else {
        $(
          'select[id^="edit-field-tags-0-subform-field-audience-selection"]'
        ).val("beneficiaries");
        $(
          'div[id^="edit-field-tags-0-subform-field-audience-beneficiares-wrapper"]'
        ).show();
        $(
          'div[id^="edit-field-tags-0-subform-field-non-beneficiares-wrapper"]'
        ).hide();
      }

      // Add required marker to fieldset. This is necessary because we cannot mark
      // any of the child fields as required.
      $("#edit-group-tags > legend").addClass("form-required");

      // Hide 'N/A' options.
      $(
        'input[id^="edit-field-tags-0-subform-field-audience-beneficiares-none"]'
      )
        .parent()
        .hide();
      $('input[id^="edit-field-tags-0-subform-field-non-beneficiares-none"]')
        .parent()
        .hide();

      // Enforce tag selection rules.
      enforceMaximumNumberOfTags();
      // React when the tags fieldset changes.
      $("fieldset#edit-group-tags").change(() => {
        const selection = $(
          'select[id^="edit-field-tags-0-subform-field-audience-selection"]'
        ).val();
        // Reset our selections and hide our vocabs by default.
        $('fieldset#edit-group-tags input[type="radio"]').attr(
          "checked",
          false
        );
        $(
          'div[id^="edit-field-tags-0-subform-field-audience-beneficiares-wrapper"]'
        ).hide();
        $(
          'div[id^="edit-field-tags-0-subform-field-non-beneficiares-wrapper"]'
        ).hide();
        if (selection === "beneficiaries") {
          // Show beneficiares.
          $(
            'div[id^="edit-field-tags-0-subform-field-audience-beneficiares-wrapper"]'
          ).show();
          // Set non-beneficiaries to none.
          $("#edit-field-tags-0-subform-field-non-beneficiares-none").attr(
            "checked",
            true
          );
        }
        if (selection === "non-beneficiaries") {
          // Show non-beneficiares.
          $(
            'div[id^="edit-field-tags-0-subform-field-non-beneficiares-wrapper"]'
          ).show();
          // Set beneficiaries to none.
          $("#edit-field-tags-0-subform-field-audience-beneficiares-none").attr(
            "checked",
            true
          );
        }
        if (selection === "_none") {
          // Set both vocabs to none.
          $("#edit-field-tags-0-subform-field-non-beneficiares-none").attr(
            "checked",
            true
          );
          $("#edit-field-tags-0-subform-field-audience-beneficiares-none").attr(
            "checked",
            true
          );
        }
        // Enforce tag selection rules.
        enforceMaximumNumberOfTags();
      });
    },
  };
})(jQuery, window.Drupal);
