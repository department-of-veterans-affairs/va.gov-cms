/**
 * @file
 */

(function ($, Drupal) {
  /**
   * Ensure that a maximum of 4 tags + audiences may be selected.
   */
  function enforceMaximumNumberOfTags() {
    // Get the total number of tags selected in the Topics fields.
    let total = $('div[id^="edit-field-tags-0-subform-field-topics"]').find('input:checked').length;

    // Prevent showing the Beneficiaries field if we already have 4 topic tags.
    if (total >= 4) {
      $('select[id^="edit-field-tags-0-subform-field-audience-selection"]').attr('disabled', true);
      $('div.form-item-field-tags-0-subform-field-audience-selection').addClass('form-disabled');
    } else {
      $('select[id^="edit-field-tags-0-subform-field-audience-selection"]').attr('disabled', false);
      $('div.form-item-field-tags-0-subform-field-audience-selection').removeClass('form-disabled');
    }
    // Find out if there is a Beneficiary/Non-beneficiary term selected and increase the total if so.
    let audienceSelected = $('div[id^="edit-field-tags-0-subform-field-audience-beneficiares-wrapper"]')
      .find('input:not([id^="edit-field-tags-0-subform-field-audience-beneficiares-none"]):checked').length;
    if ($('select[id^="edit-field-tags-0-subform-field-audience-selection"]').val() === 'non-beneficiaries') {
      audienceSelected = $('div[id^="edit-field-tags-0-subform-field-non-beneficiares-wrapper"]')
        .find('input:not([id^="edit-field-tags-0-subform-field-non-beneficiares-none"]):checked').length;
    }

    total += audienceSelected;

    if (total >= 4) {
      // If a total of four or more tags have been selected, prevent the user from selecting more.
      $('div[id^="edit-field-tags-0-subform-field-topics"]').find('input[type=checkbox]:not(:checked)').attr('disabled', true);
    } else {
      // Otherwise, ensure that more tags may be selected.
      $('div[id^="edit-field-tags-0-subform-field-topics"]').find('input[type=checkbox]').attr('disabled', false);
    }
  }

  Drupal.behaviors.vaGovAudienceTopics = {
    attach: function () {

      // Add required marker to fieldset. This is necessary because we cannot mark
      // any of the child fields as required.
      $('#edit-group-tags > legend').addClass('form-required');

      // Hide 'N/A' options.
      $('input[id^="edit-field-tags-0-subform-field-audience-beneficiares-none"]').parent().hide();
      $('input[id^="edit-field-tags-0-subform-field-non-beneficiares-none"]').parent().hide();

      // Enforce tag selection rules.
      enforceMaximumNumberOfTags();
      // React when the tags fieldset changes.
      $('fieldset#edit-group-tags').change(function () {
        const selection = $('select[id^="edit-field-tags-0-subform-field-audience-selection"]').val();

        if (selection === 'beneficiaries') {
          $('div[id^="edit-field-tags-0-subform-field-non-beneficiares-wrapper"]').hide();
          $('div[id^="edit-field-tags-0-subform-field-audience-beneficiares-wrapper"]').show();
        } else if (selection === 'non-beneficiaries') {
          $('div[id^="edit-field-tags-0-subform-field-audience-beneficiares-wrapper"]').hide();
          $('div[id^="edit-field-tags-0-subform-field-non-beneficiares-wrapper"]').show();
        } else {
          $('input[id^="edit-field-tags-0-subform-field-audience-beneficiares-none"]').prop('checked', true);
          $('input[id^="edit-field-tags-0-subform-field-non-beneficiares-none"]').prop('checked', true);
          $('div[id^="edit-field-tags-0-subform-field-audience-beneficiares-wrapper"]').hide();
          $('div[id^="edit-field-tags-0-subform-field-non-beneficiares-wrapper"]').hide();
        }

        // Enforce tag selection rules.
        enforceMaximumNumberOfTags();
      });

      // React when the tag/audience fields are changed.
      $('div[id^="edit-field-tags-0-subform-field-topics"], div[id^="edit-field-tags-0-subform-field-audience-beneficiares-wrapper"]', 'div[id^="edit-field-tags-0-subform-field-non-beneficiares-wrapper"]').find('input').change(function () {

        // Enforce tag selection rules.
        enforceMaximumNumberOfTags();
      });
    }
  };
})(jQuery, window.Drupal);
