/**
 * @file
 */

(function ($, window, Drupal) {
    Drupal.behaviors.vaGovUseFacilityHours = {
      attach: function (context, settings) {
       let hours = context.querySelector("[data-drupal-selector$='-subform-field-hours-wrapper-facility-hours']");
       let hoursSelectionSelector = context.querySelectorAll("[id$=-subform-field-hours] input");
       hoursSelectionSelector.forEach( function(hoursSelection) {
        $(hoursSelection, context).on("click", function () {
            let choice = hoursSelection.value;
            if (choice == "0") {
                $(hours).show();
            }
            else {
                $(hours).hide();
            }
            });
       });
       },
    };
  })(jQuery, window, Drupal);
