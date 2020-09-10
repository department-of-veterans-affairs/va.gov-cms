/**
 * @file
 */

(function (Drupal) {
  Drupal.behaviors.vaGovServiceLocationAddress = {
    attach: function (context, settings) {
      // Grab our address toggles.
      const checkboxes = document.querySelectorAll('.paragraph-type--service-location-address .form-checkbox');
      checkboxes.forEach(check => {
        // Grab our closest address.
        const address = check.parentElement.parentElement.nextElementSibling;
        check.addEventListener('click', event => {
          // Detemine whether or not to display after checkbox interaction.
          if (check.checked) {
            address.style.display = 'none';
          }
          else {
            address.style.display = 'block';
          }
        })
      })
    }
  };

})(Drupal);
