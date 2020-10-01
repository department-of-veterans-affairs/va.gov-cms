/**
 * @file
 */

(function (Drupal) {
  Drupal.behaviors.vaGovContactParagraphsInteractions = {
    attach: function (context, settings) {
      // Grab our phone extension toggles.
      const extensionFieldsSelects = context.querySelectorAll('.field--name-field-phone-number-type select');
      extensionFieldsSelects.forEach(ext => {
        ext.addEventListener('change', () => {
          const divWrap = ext.parentElement.parentElement.parentElement;
          const extInput = divWrap.querySelector('.field--name-field-phone-extension');
          // Toggle the ext input field depending on select list option.
          if (ext.value === 'tel') {
            extInput.style.display = 'block';
          }
          else {
            extInput.style.display = 'none';
          }
        });
      })
    }
  };

})(Drupal);
