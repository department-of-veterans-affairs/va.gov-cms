((Drupal) => {
  Drupal.behaviors.vaGovProfileBodyConditional = {
    attach() {
      const bodyWysiwygLabel = document.querySelector(
        'label[for="edit-field-body-0-value"]'
      );
      // Sometimes these classes get dropped, causing the red asterisk to
      // not appear. This is a failsafe.
      bodyWysiwygLabel.classList.add("js-form-required", "form-required");
    },
  };
})(Drupal);
