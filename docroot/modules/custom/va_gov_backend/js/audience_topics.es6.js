/**
 * @file
 */

(($, Drupal) => {

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
      });
    },
  };
})(jQuery, window.Drupal);
