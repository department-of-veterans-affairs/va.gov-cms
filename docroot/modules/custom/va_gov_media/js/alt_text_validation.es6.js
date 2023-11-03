/**
 * @file
 * Attaches behaviors VA GOv Media module.
 */
(($, Drupal, once, drupalSettings) => {
  if (typeof drupalSettings.cvJqueryValidateOptions === "undefined") {
    drupalSettings.cvJqueryValidateOptions = {};
  }

  if (drupalSettings.clientside_validation_jquery.force_validate_on_blur) {
    drupalSettings.cvJqueryValidateOptions.onfocusout = (element) => {
      // "eager" validation
      this.element(element);
    };
  }

  drupalSettings.cvJqueryValidateOptions.rules = {
    "image[0][alt]": {
      remote: {
        url: `${drupalSettings.path.baseUrl}media/validate`,
        type: "post",
        data: {
          value() {
            return $("textarea[data-drupal-selector='edit-image-0-alt']").val();
          },
        },
        dataType: "json",
      },
    },
    "media[0][fields][image][0][alt]": {
      remote: {
        url: `${drupalSettings.path.baseUrl}media/validate`,
        type: "post",
        data: {
          value() {
            return $(
              "textarea[data-drupal-selector='edit-media-0-fields-image-0-alt']"
            ).val();
          },
        },
        dataType: "json",
      },
    },
  };

  // Add messages with translations from backend.
  $.extend(
    $.validator.messages,
    drupalSettings.clientside_validation_jquery.messages
  );

  /**
   * Attaches jQuery validate behavior to forms.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *  Attaches the outline behavior to the right context.
   */
  Drupal.behaviors.altTextValidate = {
    // eslint-disable-next-line no-unused-vars
    attach(context) {
      // Allow all modules to update the validate options.
      // Example of how to do this is shown below.
      $(document).trigger(
        "cv-jquery-validate-options-update",
        drupalSettings.cvJqueryValidateOptions
      );

      // Process for all the forms on the page everytime,
      // we already use once so we should be good.
      once("altTextValidate", "body form").forEach((element) => {
        $(element).validate(drupalSettings.cvJqueryValidateOptions);
      });
    },
  };
  // eslint-disable-next-line no-undef
})(jQuery, Drupal, once, drupalSettings);
