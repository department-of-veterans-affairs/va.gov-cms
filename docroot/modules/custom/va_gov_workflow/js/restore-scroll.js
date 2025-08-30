(function (Drupal, drupalSettings, $) {
  'use strict';

  // Small behavior to save and restore window scroll position around
  // AJAX updates. This helps avoid a page "jump" when ReplaceCommand
  // or other DOM-replacing commands run.
  Drupal.behaviors.vaGovWorkflowRestoreScroll = {
    attach: function (context, settings) {
      // Ensure we bind handlers only once.
      if (this._bound) {
        return;
      }
      this._bound = true;

      var saved = null;

      // Save scroll position when any ajax request starts.
      $(document).on('ajaxSend.vaGovWorkflowRestoreScroll', function (event, jqXHR, ajaxOptions) {
        // Only capture for standard XHR requests originating from Drupal Ajax
        // calls. We keep this generic so it helps the moderation-state ReplaceCommand
        // without being tightly coupled to the module.
        saved = {
          x: (window.scrollX || window.pageXOffset || 0),
          y: (window.scrollY || window.pageYOffset || 0)
        };
      });

      // Restore scroll position after the ajax request completes and DOM
      // changes have been applied. Use requestAnimationFrame to let the
      // browser paint the updated DOM first.
      $(document).on('ajaxComplete.vaGovWorkflowRestoreScroll', function (event, jqXHR, ajaxOptions) {
        if (!saved) {
          return;
        }
        // Use rAF so we restore after DOM updates/paint. Also guard with a
        // small timeout for environments where rAF may not be sufficient.
        var restore = function () {
          try {
            window.scrollTo(saved.x, saved.y);
          } finally {
            saved = null;
          }
        };

        if (typeof window.requestAnimationFrame === 'function') {
          window.requestAnimationFrame(function () {
            // Slight timeout after rAF to be extra safe for complex layouts.
            setTimeout(restore, 0);
          });
        }
        else {
          setTimeout(restore, 0);
        }
      });
    }
  };

})(Drupal, drupalSettings, jQuery);
