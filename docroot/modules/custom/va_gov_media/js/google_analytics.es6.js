/**
 * Google Analytics tracking of media events.
 */
/* global gtag */
(function vaGovMediaIIFE(Drupal) {
  // Calls gtag for media events.
  function sendMediaEvent(eventAction, eventLabel) {
    if (typeof gtag !== "function") return;
    try {
      gtag("event", "image_upload", {
        event_category: "Media",
        event_label: eventLabel,
        event_action: eventAction,
      });
    } catch (err) {
      // Swallow any gtag runtime errors to avoid breaking admin UI.
    }
  }

  function trackAddMediaClick() {
    sendMediaEvent("add_media_click", "add_media_button");
  }
  function trackAltFieldChanged() {
    sendMediaEvent("alt_field_changed", "alt_field");
  }
  function trackAiAltGenerationClick() {
    sendMediaEvent("ai_generate_click", "ai_alt_generation");
  }
  function trackSubmitClick() {
    sendMediaEvent("submit_click", "submit");
  }

  // Attach event listeners via Drupal behaviors so this works with AJAX.
  Drupal.behaviors.vaGovMedia = {
    attach: function vaGovMediaAttach() {
      function isActivationEvent(e) {
        const key = e && e.key;
        const keyCode = e && e.keyCode;
        return (
          e.type === "mousedown" ||
          e.type === "touchstart" ||
          key === "Enter" ||
          key === " " ||
          key === "Spacebar" ||
          keyCode === 13 ||
          keyCode === 32
        );
      }

      // Add media open button: pure delegated handling attached once.
      function delegatedAddMediaHandler(e) {
        const addMediaSelector =
          "input[data-drupal-selector$='field-media-open-button']";
        const button =
          e.target && e.target.closest && e.target.closest(addMediaSelector);
        if (!button || !isActivationEvent(e)) return;
        trackAddMediaClick();
      }

      if (!document.vaGovMediaAddDelegated) {
        document.vaGovMediaAddDelegated = true;
        document.addEventListener("mousedown", delegatedAddMediaHandler, true);
        document.addEventListener("touchstart", delegatedAddMediaHandler, true);
        document.addEventListener("keydown", delegatedAddMediaHandler, true);
      }

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
              // If the value changed since focus, track the alt-field change.
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
        const button =
          e.target &&
          e.target.closest &&
          e.target.closest(aiAltTextRegenerateSelector);
        if (!button || !isActivationEvent(e)) return;
        trackAiAltGenerationClick();
      }

      if (!document.vaGovMediaAiRegenerateDelegated) {
        document.vaGovMediaAiRegenerateDelegated = true;

        document.addEventListener(
          "mousedown",
          delegatedAltTextRegenerateHandler,
          true
        );
        document.addEventListener(
          "touchstart",
          delegatedAltTextRegenerateHandler,
          true
        );
        document.addEventListener(
          "keydown",
          delegatedAltTextRegenerateHandler,
          true
        );
      }

      // Submit tracking: listen for form submit
      function delegatedSubmitClickHandler(e) {
        const submitSelector =
          "button.js-form-submit.form-submit:not(.ai-alt-text-generation):not([data-drupal-selector*='ai-alt-text-generation'])";
        const button =
          e.target && e.target.closest && e.target.closest(submitSelector);
        if (!button) return;
        trackSubmitClick();
      }

      if (!document.vaGovMediaSubmitDelegated) {
        document.vaGovMediaSubmitDelegated = true;
        document.addEventListener("click", delegatedSubmitClickHandler, true);
      }
    },
  };
})(Drupal);
