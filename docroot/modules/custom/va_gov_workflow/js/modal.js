(function (Drupal, drupalSettings, $) {
  'use strict';

  Drupal.behaviors.vaGovWorkflowModal = {
    attach: function (context, settings) {
      // Wire up any archive modal action buttons rendered by the server.
      // Use the global once() function provided by the core/drupal.once
      // library. It returns a list of elements that have not yet been
      // processed for the provided key.
      var processed = once('vaGovWorkflowModal', '.va-gov-workflow-modal-actions .va-gov-workflow-archive', context);
      Array.prototype.forEach.call(processed, function (el) {
        el.addEventListener('click', function (e) {
          e.preventDefault();
          var submitSelector = el.getAttribute('data-submit-selector') || (drupalSettings.vaGovWorkflow && drupalSettings.vaGovWorkflow.submitSelector) || '#edit-submit';
          // Trigger the configured submit element so the modal action flows
          // through Drupal form submission (and the AJAX callback).
          var submitEl = document.querySelector(submitSelector);
          if (submitEl) {
            submitEl.click();
          }
        });
      });
    }
  };

})(Drupal, drupalSettings, jQuery);
