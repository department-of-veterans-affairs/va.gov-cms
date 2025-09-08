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
    },
  };
})(jQuery, once, Drupal);
