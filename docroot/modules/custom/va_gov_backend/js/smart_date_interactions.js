/**
 * @file
 */

(function (Drupal) {
  Drupal.behaviors.smartDateInteractions = {
    attach: function (context, settings) {
      const timeEnd = document.querySelector('.time-end');

      if (timeEnd !== null && typeof timeEnd === 'object' && timeEnd.classList.contains('hidden')) {
        const alldayLabels = document.querySelectorAll('.allday-label');

        alldayLabels.forEach(label => {
          label.style.display = 'none';
          label.classList.add('hidden');
        });
      }
    }
  };

})(Drupal);
