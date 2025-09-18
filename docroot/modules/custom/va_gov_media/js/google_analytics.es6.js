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
    console.log('vaGovMedia: trackAddMediaClick called with label:', label);
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
      // const addMediaSelector = '[data-drupal-selector="edit-field-media-open-button"],#edit-field-media-open-button';
      // All Add Media button clicks are now handled by a single delegated event listener below.

      // function delegatedAddMediaClick(e) {
      //   console.log('vaGovMedia: delegatedAddMediaClick fired', e.target);
      //   const { target } = e;
      //   const match = target.closest ? target.closest(addMediaSelector) : null;
      //   if (match) {
      //     const label = match.getAttribute("aria-label") || match.textContent.trim();
      //     try {
      //       /* eslint-disable-next-line no-console */
      //       console.debug("vaGovMedia: delegated add media click", label);
      //     } catch (_err) {
      //       /* ignore */
      //     }
      //     trackAddMediaClick(label);
      //   }
      // }
      // try {
      //   console.log('vaGovMedia: registering delegatedAddMediaClick');
      //   document.addEventListener("mousedown", delegatedAddMediaClick);
      // } catch (_err) {
        /* ignore delegated attach failures */
      // }
      const myButton = document.querySelector('input[data-drupal-selector="edit-field-media-open-button"]');

      // Check if the element exists and the event listener hasn't been added yet.
      if (myButton && !myButton.dataset.listenerAttached) {
        myButton.addEventListener('mousedown', function(event) {
          // Your code to execute on mousedown.
          console.log('Button mousedown!');
          trackAddMediaClick("cbu04905");
        });
        myButton.addEventListener('touchstart', function(event) {
          // Your code to execute on touchstart.
          console.log('Button touchstart!');
          trackAddMediaClick("cbu04905");
        });
        myButton.addEventListener('keydown', function(event) {
          // Your code to execute on keydown.
          console.log('Button keydown!');
          trackAddMediaClick("cbu04905");
        });

        // Mark the element to prevent re-attaching on subsequent AJAX calls.
        myButton.dataset.listenerAttached = true;
      }
    },
  };
})(jQuery, once, Drupal);
