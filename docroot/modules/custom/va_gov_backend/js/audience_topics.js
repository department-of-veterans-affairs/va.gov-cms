/**
 * @file
 */

(function ($, Drupal) {
  /**
   * Ensure that a maximum of 4 tags + audiences may be selected.
   */
  function enforceMaximumNumberOfTags() {
    // Get the total number of tags selected between in the Topics and Beneficiaries fields.
    let total = $('div[id^="edit-field-tags-0-subform-field-topics"]').find('input:checked').length;
    const audienceSelected = $('div[id^="edit-field-tags-0-subform-field-audience-beneficiares-wrapper"]')
      .find('input:not([id^="edit-field-tags-0-subform-field-audience-beneficiares-none"]):checked').length;
    total += audienceSelected;

    if (total >= 4) {
      // If a total of four or more tags have been selected, prevent the user from selecting more.
      $('div[id^="edit-field-tags-0-subform-field-topics"]').find('input[type=checkbox]:not(:checked)').attr('disabled', true);
      $('div[id^="edit-field-tags-0-subform-field-audience-beneficiares-wrapper"]')
        .find('input:not([id^="edit-field-tags-0-subform-field-audience-beneficiares-none"]):checked').attr('disabled', true);
    } else {
      // Otherwise, ensure that more may be selected.
      $('div[id^="edit-field-tags-0-subform-field-topics"]').find('input[type=checkbox]').attr('disabled', false);
      $('div[id^="edit-field-tags-0-subform-field-audience-beneficiares-wrapper"]')
        .find('input').attr('disabled', false);
    }
  }

  Drupal.behaviors.vaGovAudienceTopics = {
    attach: function () {

      // Add required marker to fieldset. This is necessary because we cannot mark
      // any of the child fields as required.
      $('#edit-group-tags > legend').addClass('form-required');

      // Hide the beneficiaries field when the audience select does not have benficiaries selected.
      if ($('select[id^="edit-field-tags-0-subform-field-audience-selection"]').val() !== 'beneficiaries') {
        $('div[id^="edit-field-tags-0-subform-field-audience-beneficiares-wrapper"]').hide();
      }

      // Hide the 'N/A' option for the Beneficiaries field.
      $('input[id^="edit-field-tags-0-subform-field-audience-beneficiares-none"]').parent().hide();

      // Disable the "Non-beneficiaries" option in the field_audience_selection dropdown.
      $('select[id^="edit-field-tags-0-subform-field-audience-selection"] option[value="non-beneficiaries"]').attr('disabled', true);

      // Enforce tag selection rules.
      enforceMaximumNumberOfTags();

      // React when the Audience dropdown is changed.
      $('select[id^="edit-field-tags-0-subform-field-audience-selection"]').change(function () {
        const selection = $(this).val();

        if (selection === 'beneficiaries') {
          // Show beneficiaries field when selected.
          $('div[id^="edit-field-tags-0-subform-field-audience-beneficiares-wrapper"]').show();
        } else {
          // Otherwise clear any selected value in the beneficiaries field and hide it.
          $('input[id^="edit-field-tags-0-subform-field-audience-beneficiares-none"]').prop('checked', true);
          $('div[id^="edit-field-tags-0-subform-field-audience-beneficiares-wrapper"]').hide();
        }

        // Enforce tag selection rules.
        enforceMaximumNumberOfTags();
      });

      // React when the tag/audience fields are changed.
      $('div[id^="edit-field-tags-0-subform-field-topics"], div[id^="edit-field-tags-0-subform-field-audience-beneficiares-wrapper"]').find('input').change(function () {

        // Enforce tag selection rules.
        enforceMaximumNumberOfTags();
      });
    }
  };
})(jQuery, window.Drupal);
