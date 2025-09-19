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
    console.log("vaGovMedia: trackAddMediaClick called with label:", label);
    if (typeof gtag === "function") {
      gtag("event", "image_upload", {
        event_category: "Media",
        event_label: label || "add_media_button",
        upload_action: "add_media_click",
      });
    }
  }
  function trackAltFieldChanged() {
    if (typeof gtag === "function") {
      gtag("event", "image_upload", {
        event_category: "Media",
        event_label: "alt_field",
        upload_action: "alt_field_changed",
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
      console.log("vaGovMedia: attach called");

      function attachEventToAll(selector, eventType, handler, rootContext) {
        const root =
          rootContext && typeof rootContext.querySelector === "function"
            ? rootContext
            : document;

        function attachIfNeeded(el) {
          if (!el.dataset[`vaGovMediaAttached${eventType}`]) {
            el.addEventListener(eventType, handler);
            el.dataset[`vaGovMediaAttached${eventType}`] = "1";
            console.log(
              `[vaGovMedia] Attached '${eventType}' to`,
              el,
              "with selector",
              selector
            );
          }
        }

        // Attach to all currently-present nodes
        Array.from(root.querySelectorAll(selector)).forEach(attachIfNeeded);

        // Helper to process a node and its descendants
        function processNode(node) {
          if (node.nodeType !== 1) return;
          if (node.matches && node.matches(selector)) {
            attachIfNeeded(node);
          }
          if (node.querySelectorAll) {
            Array.from(node.querySelectorAll(selector)).forEach(attachIfNeeded);
          }
        }

        // Observe future nodes and attach when they appear
        const mo = new MutationObserver((mutations) => {
          mutations.forEach((mutation) => {
            Array.from(mutation.addedNodes).forEach(processNode);
          });
        });
        mo.observe(root, { childList: true, subtree: true });
      }

      // Add Media button
      function handleAddMediaMouseDown(e) {
        const label =
          e.target.getAttribute("aria-label") || e.target.textContent.trim();
        trackAddMediaClick(label);
      }
      function handleAddMediaTouchStart(e) {
        const label =
          e.target.getAttribute("aria-label") || e.target.textContent.trim();
        trackAddMediaClick(label);
      }
      function handleAddMediaKeyDown(e) {
        const label =
          e.target.getAttribute("aria-label") || e.target.textContent.trim();
        trackAddMediaClick(label);
      }
      function handleAltFieldChanged() {
        trackAltFieldChanged();
      }
      function handleAiAltGenerationClick() {
        trackAiAltGenerationClick();
      }
      function handleSubmitMouseDown() {
        trackSubmitClick();
      }
      function handleSubmitTouchStart() {
        trackSubmitClick();
      }
      function handleSubmitKeyDown() {
        trackSubmitClick();
      }

      attachEventToAll(
        "input[data-drupal-selector$='field-media-open-button']",
        "mousedown",
        handleAddMediaMouseDown,
        context
      );
      attachEventToAll(
        "input[data-drupal-selector$='field-media-open-button']",
        "touchstart",
        handleAddMediaTouchStart,
        context
      );
      attachEventToAll(
        "input[data-drupal-selector$='field-media-open-button']",
        "keydown",
        handleAddMediaKeyDown,
        context
      );

      // Alt text field event delegation
      function delegatedAltFieldChangeHandler(e) {
        const field = e.target.closest(
          'input[data-drupal-selector$="edit-media-0-fields-image-0-alt"]'
        );
        if (field) {
          console.log(
            "[vaGovMedia] Delegated alt text change fired for",
            field
          );
          trackAltFieldChanged();
        }
      }
      document.addEventListener("change", delegatedAltFieldChangeHandler);

      // AI alt generation button
      // attachEventToAll(
      //   "input[data-drupal-selector$='edit-media-0-fields-image-0-ai-alt-text-generation-0']",
      //   "mousedown",
      //   handleAiAltGenerationClick,
      //   context
      // );
      // // AI alt generation button
      // attachEventToAll(
      //   "input[data-drupal-selector$='edit-media-0-fields-image-0-ai-alt-text-generation-0']",
      //   "touchstart",
      //   handleAiAltGenerationClick,
      //   context
      // );
      // attachEventToAll(
      //   "input[data-drupal-selector$='edit-media-0-fields-image-0-ai-alt-text-generation-0']",
      //   "keydown",
      //   handleAiAltGenerationClick,
      //   context
      // );

      // Submit button event delegation
      function delegatedAltTextRegenerateHandler(e) {
        const button = e.target.closest("input[data-drupal-selector$='edit-media-0-fields-image-0-ai-alt-text-generation-0']");
        if (button) {
          console.log(
            "[vaGovMedia] Delegated handler fired for",
            button,
            "event type:",
            e.type
          );
          if (
            e.type === "mousedown" ||
            e.type === "touchstart" ||
            e.type === "keydown"
          ) {
            handleAiAltGenerationClick();
          }
        }
      }
      document.addEventListener("mousedown", delegatedAltTextRegenerateHandler);
      document.addEventListener("touchstart", delegatedAltTextRegenerateHandler);
      document.addEventListener("keydown", delegatedAltTextRegenerateHandler);

      // Submit button event delegation
      function delegatedSubmitHandler(e) {
        const button = e.target.closest("button.js-form-submit.form-submit");
        if (button) {
          console.log(
            "[vaGovMedia] Delegated handler fired for",
            button,
            "event type:",
            e.type
          );
          if (
            e.type === "mousedown" ||
            e.type === "touchstart" ||
            e.type === "keydown"
          ) {
            trackSubmitClick();
          }
        }
      }
      document.addEventListener("mousedown", delegatedSubmitHandler);
      document.addEventListener("touchstart", delegatedSubmitHandler);
      document.addEventListener("keydown", delegatedSubmitHandler);
    },
  };
})(jQuery, once, Drupal);
