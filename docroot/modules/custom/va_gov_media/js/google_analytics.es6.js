/**
 * Google Analytics tracking of media events.
 */
/* global gtag */
//* global gtag once */
(function vaGovMediaIIFE($, once, Drupal) {
  function trackUploadFileSelection(fileName) {
    if (typeof gtag === "function") {
      gtag("event", "image_upload", {
        event_category: "Media",
        event_label: fileName || "file_selected",
        upload_action: "file_selected",
      });
    }
  }
  function trackUploadButtonClick(label) {
    if (typeof gtag === "function") {
      gtag("event", "image_upload", {
        event_category: "Media",
        event_label: label || "upload_button",
        upload_action: "button_click",
      });
    }
  }
  function trackAddMediaClick(label) {
    if (typeof gtag === "function") {
      gtag("event", "image_upload", {
        event_category: "Media",
        event_label: label || "add_media_button",
        upload_action: "add_media_click",
      });
    }
  }
  function trackAltFieldFocus() {
    if (typeof gtag === "function") {
      gtag("event", "image_upload", {
        event_category: "Media",
        event_label: "alt_field",
        upload_action: "alt_field_focus",
      });
    }
  }
  function trackAiAltGenerationClick() {
    if (typeof gtag === "function") {
      gtag("event", "image_upload", {
        event_category: "Media",
        event_label: "ai_alt_generation",
        upload_action: "ai_generate_click",
      });
    }
  }
  function trackSubmitClick() {
    if (typeof gtag === "function") {
      gtag("event", "image_upload", {
        event_category: "Media",
        event_label: "submit",
        upload_action: "submit_click",
      });
    }
  }

  // Attach event listeners via Drupal behaviors so this works with AJAX.
  Drupal.behaviors.vaGovMedia = {
    attach: function vaGovMediaAttach(context) {
      console.log('vaGovMedia: attach called');
      const addMediaSelector = '[data-drupal-selector="edit-field-media-open-button"],#edit-field-media-open-button';
      // All Add Media button clicks are now handled by a single delegated event listener below.

      function delegatedAddMediaClick(e) {
        console.log('vaGovMedia: delegatedAddMediaClick fired', e.target);
        const { target } = e;
        const match = target.closest ? target.closest(addMediaSelector) : null;
        if (match) {
          const label = match.getAttribute("aria-label") || match.textContent.trim();
          try {
            /* eslint-disable-next-line no-console */
            console.debug("vaGovMedia: delegated add media click", label);
          } catch (_err) {
            /* ignore */
          }
          trackAddMediaClick(label);
        }
      }
      try {
        console.log('vaGovMedia: registering delegatedAddMediaClick');
        document.addEventListener("click", delegatedAddMediaClick);
      } catch (_err) {
        /* ignore delegated attach failures */
      }
    },
  };
})(jQuery, once, Drupal);
