/**
 * @file
 */

(function ($, window, Drupal) {
    const displayHours = (toggle, table) => {
        if (toggle.checked) {
          if (toggle.value === "0") {
            $(table).show();
          } else {
            $(table).hide();
          }
        }
      };

    Drupal.behaviors.vaGovUseFacilityHours = {
      attach: function (context, settings) {
       let hours = context.querySelector("[data-drupal-selector$='-subform-field-hours-wrapper-facility-hours']");
       let hoursSelectionSelector = context.querySelectorAll("[id$=-subform-field-hours] input");
       hoursSelectionSelector.forEach( function(hoursSelection) {
        window.addEventListener("load", () => {
            // Determine whether or not to display on load.
            displayHours(hoursSelection, hours);
          });
        $(hoursSelection, context).on("click", () =>  {
            displayHours(hoursSelection, hours);
            });
       });
       },
    };
  })(jQuery, window, Drupal);
