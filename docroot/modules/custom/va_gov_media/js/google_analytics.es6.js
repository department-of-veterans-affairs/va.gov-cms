/**
 * Google Analytics tracking of media events.
 */
/* global gtag */
(($, once, Drupal) => {
  // Track uploads: file selection and upload button clicks.
  const trackUploadFileSelection = (fileName) => {
    if (typeof gtag === "function") {
      gtag("event", "image_upload", {
        event_category: "Media",
        event_label: fileName || "file_selected",
        upload_action: "file_selected",
      });
    }
  };

  const trackUploadButtonClick = (label) => {
    if (typeof gtag === "function") {
      gtag("event", "image_upload", {
        event_category: "Media",
        event_label: label || "upload_button",
        upload_action: "button_click",
      });
    }
  };

  // Track when the user focuses/clicks the image alt text field.
  const trackAltFieldFocus = () => {
    if (typeof gtag === "function") {
      gtag("event", "image_upload", {
        event_category: "Media",
        event_label: "alt_field",
        upload_action: "alt_field_focus",
      });
    }
  };

  // Track clicks on the AI alt-text generation control.
  const trackAiAltGenerationClick = () => {
    if (typeof gtag === "function") {
      gtag("event", "image_upload", {
        event_category: "Media",
        event_label: "ai_alt_generation",
        upload_action: "ai_generate_click",
      });
    }
  };

  // Track clicks on the AI alt-text generation control.
  const trackSubmitClick = () => {
    if (typeof gtag === "function") {
      gtag("event", "image_upload", {
        event_category: "Media",
        event_label: "submit",
        upload_action: "submit_click",
      });
    }
  };

  // Attach event listeners via Drupal behaviors so this works with AJAX.
  Drupal.behaviors.vaGovMedia = {
    attach(context) {
      // Debug: indicate behavior attachment and context.
      try {
        /* eslint-disable-next-line no-console */
        console.debug("vaGovMedia behavior attach", context);
      } catch (_e) {
        /* ignore */
      }

      // Only run this behavior on the media image add form to avoid
      // instrumenting unrelated pages. Check the provided context first
      // (for AJAX attachments), then fall back to the document.
      const formSelector = '[data-drupal-selector="media-image-add-form"]';
      const hasMediaForm =
        (context &&
          typeof context.querySelector === "function" &&
          context.querySelector(formSelector)) ||
        document.querySelector(formSelector);
      if (!hasMediaForm) {
        return;
      }

      // File input change: capture selected filename.
      const fileInputs = once(
        "va-gov-media-upload-file",
        'input[type="file"]',
        context || document
      );
      try {
        /* eslint-disable-next-line no-console */
        console.debug(
          "vaGovMedia: file inputs found",
          fileInputs && fileInputs.length ? fileInputs.length : fileInputs
        );
      } catch (_e) {
        /* ignore */
      }
      fileInputs.forEach((input) => {
        input.addEventListener("change", (e) => {
          const f =
            e.target.files && e.target.files[0] ? e.target.files[0].name : "";
          try {
            /* eslint-disable-next-line no-console */
            console.debug("vaGovMedia: file input change", f);
          } catch (_err) {
            /* ignore */
          }
          trackUploadFileSelection(f);
        });
      });

      // Upload buttons: common selectors used in media upload UIs.
      const buttonSelector = 'input[accept="image/*"]';
      const uploadButtons = once(
        "va-gov-media-upload-btn",
        buttonSelector,
        context || document
      );
      try {
        /* eslint-disable-next-line no-console */
        console.debug(
          "vaGovMedia: upload buttons found",
          uploadButtons && uploadButtons.length
            ? uploadButtons.length
            : uploadButtons
        );
      } catch (_e) {
        /* ignore */
      }
      uploadButtons.forEach((btn) => {
        btn.addEventListener("click", () => {
          // Prefer aria-label, then text content.
          const label =
            btn.getAttribute("aria-label") || btn.textContent.trim();
          try {
            /* eslint-disable-next-line no-console */
            console.debug("vaGovMedia: upload button clicked", label);
          } catch (_err) {
            /* ignore */
          }
          trackUploadButtonClick(label);
        });
      });

      // If no explicit upload buttons were found, add a delegated click
      // listener as a fallback so clicks on dynamically-rendered buttons are
      // still captured.
      if (!uploadButtons || uploadButtons.length === 0) {
        try {
          /* eslint-disable-next-line no-console */
          console.debug(
            "vaGovMedia: no upload buttons found; adding delegated listener for",
            buttonSelector
          );
        } catch (_e) {
          /* ignore */
        }
        document.addEventListener("click", function delegatedUploadClick(e) {
          const { target } = e;
          const match = target.closest ? target.closest(buttonSelector) : null;
          if (match) {
            const label =
              match.getAttribute("aria-label") || match.textContent.trim();
            try {
              /* eslint-disable-next-line no-console */
              console.debug("vaGovMedia: delegated upload click", label);
            } catch (_err) {
              /* ignore */
            }
            trackUploadButtonClick(label);
          }
        });
      }

      // Track focus/click on the image alt field used in media upload forms.
      const altSelector = '[data-drupal-selector="edit-image-0-alt"]';
      const altFields = once(
        "va-gov-media-alt-field",
        altSelector,
        context || document
      );
      if (altFields && altFields.length) {
        altFields.forEach((el) => {
          el.addEventListener("focus", () => trackAltFieldFocus());
          el.addEventListener("click", () => trackAltFieldFocus());
        });
      } else {
        // Delegated listener: capture focusin events for dynamically rendered
        // fields.
        try {
          document.addEventListener("focusin", function delegatedAltFocus(e) {
            const { target } = e;
            const match = target.closest ? target.closest(altSelector) : null;
            if (match) {
              trackAltFieldFocus();
              try {
                /* eslint-disable-next-line no-console */
                console.debug("vaGovMedia: alt text field clicked");
              } catch (_err) {
                /* ignore */
              }
            }
          });
        } catch (_err) {
          /* ignore delegated attach failures */
        }
      }

      // Track AI alt-text generation button if present.
      const aiSelector =
        '[data-drupal-selector="edit-image-0-ai-alt-text-generation-0"]';
      const aiButtons = once(
        "va-gov-media-ai-alt-btn",
        aiSelector,
        context || document
      );
      if (aiButtons && aiButtons.length) {
        aiButtons.forEach((btn) => {
          btn.addEventListener("click", () => trackAiAltGenerationClick());
        });
      } else {
        // Delegated listener for dynamically-rendered AI controls.
        try {
          document.addEventListener("click", function delegatedAiClick(e) {
            const { target } = e;
            const match = target.closest ? target.closest(aiSelector) : null;
            if (match) {
              trackAiAltGenerationClick();
              try {
                /* eslint-disable-next-line no-console */
                console.debug("vaGovMedia: regenerate alt text clicked");
              } catch (_err) {
                /* ignore */
              }
            }
          });
        } catch (_err) {
          /* ignore delegated attach failures */
        }
      }

      // Track clicks on the form submit button.
      const submitSelector = '[data-drupal-selector="edit-submit"]';
      const submitButtons = once(
        "va-gov-media-submit-btn",
        submitSelector,
        context || document
      );
      if (submitButtons && submitButtons.length) {
        submitButtons.forEach((btn) => {
          btn.addEventListener("click", () => trackSubmitClick());
        });
      } else {
        try {
          document.addEventListener("click", function delegatedSubmitClick(e) {
            const { target } = e;
            const match = target.closest
              ? target.closest(submitSelector)
              : null;
            if (match) {
              trackSubmitClick();
              try {
                /* eslint-disable-next-line no-console */
                console.debug("vaGovMedia: submit button clicked");
              } catch (_err) {
                /* ignore */
              }
            }
          });
        } catch (_err) {
          /* ignore delegated attach failures */
        }
      }
    },
  };
})(jQuery, once, Drupal);
