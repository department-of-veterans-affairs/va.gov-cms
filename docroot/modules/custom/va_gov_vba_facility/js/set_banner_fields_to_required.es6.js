((Drupal) => {
  Drupal.behaviors.vaGovVbaBannerFieldsConditional = {
    attach() {
      // Make the Banner type required-looking.
      if (
        document.querySelector(
          'fieldset[data-drupal-selector="edit-field-dismissible-option"] legend span'
        )
      ) {
        const dismissibleOption = document.querySelector(
          'fieldset[data-drupal-selector="edit-field-dismissible-option"] legend span'
        );
        // Sometimes these classes get dropped, causing the red asterisk to
        // not appear. This is a failsafe.
        dismissibleOption.classList.add("js-form-required", "form-required");
      }
      // Make the Banner content required-looking.
      if (
        document.querySelector('label[for="edit-field-banner-content-0-value"]')
      ) {
        const bannerContentWysiwygLabel = document.querySelector(
          'label[for="edit-field-banner-content-0-value"]'
        );
        // Sometimes these classes get dropped, causing the red asterisk to
        // not appear. This is a failsafe.
        bannerContentWysiwygLabel.classList.add(
          "js-form-required",
          "form-required"
        );
      }
    },
  };
})(Drupal);
