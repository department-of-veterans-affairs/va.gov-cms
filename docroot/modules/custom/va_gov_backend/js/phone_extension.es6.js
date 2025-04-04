/**
 * @file
 */

(($, Drupal, once) => {
  /**
   * Callback for key presses for phone extension field.
   *
   * @param {object} e - The event.
   */
  const handlePhoneExtensionMaskKeyPress = (e) => {
    const key = e.keyCode;
    // Allow: backspace (8), tab (9), enter (13), left arrow (37), right arrow (39), delete (46)
    if ([8, 9, 13, 37, 39, 46].includes(key)) {
      return;
    }

    // Allow: numbers (0-9).
    if (key >= 48 && key <= 57) {
      return;
    }

    // Prevent everything else.
    e.preventDefault();
  };

  Drupal.behaviors.phoneExtensionMask = {
    attach(context) {
      const selector =
        "input[data-field-definition-id=paragraph--phone_number--field_phone_extension]";
      $(once("phone-extension-mask", selector), context).each(() => {
        document
          .querySelector(selector)
          .addEventListener("keydown", handlePhoneExtensionMaskKeyPress);
      });
    },
  };
})(jQuery, Drupal, once);
