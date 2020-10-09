/**
 * @file
 */

(function (Drupal) {
  Drupal.behaviors.vaGovServiceLocationAddress = {
    attach: function (context, settings) {
      // Look to see if container div for end time has hidden class.
      // If it does, hode the All day checkbox.
      const timeEnd = document.querySelector('.time-end');
      if (timeEnd.classList.contains('hidden')) {
        const alldayLabels = document.querySelectorAll('.allday-label');
        alldayLabels.forEach(label => {
          label.style.display = 'none';
          label.classList.add('hidden');
        })
        console.log('toggles: ', alldayLabels);

        //context.querySelectorAll('.allday-label').classList.add('hidden');
      }

    }
  };

})(Drupal);
