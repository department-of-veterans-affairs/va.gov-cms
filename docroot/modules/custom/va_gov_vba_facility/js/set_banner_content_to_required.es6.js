((Drupal) => {
  Drupal.behaviors.vaGovVbaBannerContentConditional = {
    attach() {
      if (document.querySelector('label[for="edit-field-banner-content-0-value"]')) {
        const bannerContentWysiwygLabel = document.querySelector(
        'label[for="edit-field-banner-content-0-value"]'
      );
      // Sometimes these classes get dropped, causing the red asterisk to
      // not appear. This is a failsafe.
      bannerContentWysiwygLabel.classList.add("js-form-required", "form-required");
      }
    },
  };
})(Drupal);
