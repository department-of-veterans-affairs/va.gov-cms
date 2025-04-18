/**
* DO NOT EDIT THIS FILE.
* See the following change record for more information,
* https://www.drupal.org/node/2815083
* @preserve
**/
(function ($, Drupal, once) {
  var handlePhoneExtensionMaskKeyPress = function handlePhoneExtensionMaskKeyPress(e) {
    var key = e.keyCode;
    if ([8, 9, 13, 37, 39, 46].includes(key)) {
      return;
    }
    if (key >= 48 && key <= 57) {
      return;
    }
    e.preventDefault();
  };
  Drupal.behaviors.phoneExtensionMask = {
    attach: function attach(context) {
      var selector = "input[data-field-definition-id=paragraph--phone_number--field_phone_extension]";
      $(once("phone-extension-mask", selector), context).each(function () {
        document.querySelector(selector).addEventListener("keydown", handlePhoneExtensionMaskKeyPress);
      });
    }
  };
})(jQuery, Drupal, once);