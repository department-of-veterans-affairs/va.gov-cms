/**
 * Google Analytics tracking of media events.
 */
/* global gtag */
//* global gtag once */
(function vaGovMediaIIFE($, once, Drupal) {
  function trackAddMediaClick() {
    if (typeof gtag === "function") {
      gtag("event", "image_upload", {
        event_category: "Media",
        event_label: "add_media_button",
        event_action: "add_media_click",
      });
    }
  }
  function trackAltFieldChanged() {
    if (typeof gtag === "function") {
      gtag("event", "image_upload", {
        event_category: "Media",
        event_label: "alt_field",
        event_action: "alt_field_changed",
      });
    }
  }
  function trackAiAltGenerationClick() {
    if (typeof gtag === "function") {
      gtag("event", "image_upload", {
        event_category: "Media",
        event_label: "ai_alt_generation",
        event_action: "ai_generate_click",
      });
    }
  }
  function trackSubmitClick() {
    if (typeof gtag === "function") {
      gtag("event", "image_upload", {
        event_category: "Media",
        event_label: "submit",
        event_action: "submit_click",
      });
    }
  }

  // Attach event listeners via Drupal behaviors so this works with AJAX.
  Drupal.behaviors.vaGovMedia = {
    attach: function vaGovMediaAttach(context) {
      function attachEventToAll(selector, eventType, handler, rootContext) {
        const root =
          rootContext && typeof rootContext.querySelector === "function"
            ? rootContext
            : document;

        function attachIfNeeded(el) {
          if (!el.dataset[`vaGovMediaAttached${eventType}`]) {
            el.addEventListener(eventType, handler);
            el.dataset[`vaGovMediaAttached${eventType}`] = "1";
          }
        }

        // Attach to all currently-present nodes.
        Array.from(root.querySelectorAll(selector)).forEach(attachIfNeeded);

        // Helper to process a node and its descendants.
        function processNode(node) {
          if (node.nodeType !== 1) return;
          if (node.matches && node.matches(selector)) {
            attachIfNeeded(node);
          }
          if (node.querySelectorAll) {
            Array.from(node.querySelectorAll(selector)).forEach(attachIfNeeded);
          }
        }

        // Observe future nodes and attach when they appear.
        const mo = new MutationObserver((mutations) => {
          mutations.forEach((mutation) => {
            Array.from(mutation.addedNodes).forEach(processNode);
          });
        });
        mo.observe(root, { childList: true, subtree: true });
      }

      // Add media button delegation (mousedown/touchstart/keydown based).
      attachEventToAll(
        "input[data-drupal-selector$='field-media-open-button']",
        "mousedown",
        trackAddMediaClick,
        context
      );
      attachEventToAll(
        "input[data-drupal-selector$='field-media-open-button']",
        "touchstart",
        trackAddMediaClick,
        context
      );
      attachEventToAll(
        "input[data-drupal-selector$='field-media-open-button']",
        "keydown",
        trackAddMediaClick,
        context
      );

      // Alt text field delegation (focus/blur based).
      if (!document.vaGovMediaAltDelegated) {
        document.vaGovMediaAltDelegated = true;
        const altSelector =
          'input[data-drupal-selector$="edit-media-0-fields-image-0-alt"]';

        // Focus/blur approach: remember the field's value on focus
        // and compare on blur.
        const preFocusValue = new WeakMap();

        document.addEventListener(
          "focusin",
          function vaGovMediaAltFocusIn(e) {
            try {
              const field =
                e.target && e.target.closest && e.target.closest(altSelector);
              if (!field) return;
              preFocusValue.set(field, field.value);
            } catch (err) {
              // ignore
            }
          },
          true
        );

        document.addEventListener(
          "focusout",
          function vaGovMediaAltFocusOut(e) {
            try {
              const field =
                e.target && e.target.closest && e.target.closest(altSelector);
              if (!field) return;
              const before = preFocusValue.get(field);
              if (before === undefined) {
                // no recorded focus value — treat change event as a potential edit
                if (field.value && field.value.length) {
                  trackAltFieldChanged();
                }
                return;
              }
              if (field.value !== before) {
                trackAltFieldChanged();
              }
              preFocusValue.delete(field);
            } catch (err) {
              // ignore
            }
          },
          true
        );
      }

      // AI alt regenerate delegated handler: broaden selectors and attach once.
      function delegatedAltTextRegenerateHandler(e) {
        const aiAltTextRegenerateSelector =
          "[data-drupal-selector*='ai-alt-text-generation']";
        const button = e.target.closest(aiAltTextRegenerateSelector);
        if (button) {
          if (
            e.type === "mousedown" ||
            e.type === "touchstart" ||
            e.type === "keydown"
          ) {
            trackAiAltGenerationClick();
          }
        }
      }

      if (!document.vaGovMediaAiRegenerateDelegated) {
        document.vaGovMediaAiRegenerateDelegated = true;

        document.addEventListener(
          "mousedown",
          delegatedAltTextRegenerateHandler
        );
        document.addEventListener(
          "touchstart",
          delegatedAltTextRegenerateHandler
        );
        document.addEventListener("keydown", delegatedAltTextRegenerateHandler);
      }

      // Submit tracking: listen for form submit
      function delegatedSubmitClickHandler(e) {
        const submitSelector =
          "button.js-form-submit.form-submit:not(.ai-alt-text-generation):not([data-drupal-selector*='ai-alt-text-generation'])";
        const button = e.target.closest(submitSelector);
        if (!button) return;
        trackSubmitClick();
      }

      if (!document.vaGovMediaSubmitDelegated) {
        document.vaGovMediaSubmitDelegated = true;
        document.addEventListener("click", delegatedSubmitClickHandler, true);
      }
    },
  };
})(jQuery, once, Drupal);
