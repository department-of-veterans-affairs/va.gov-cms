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
      // const myButton = document.querySelector(
      //   'input[data-drupal-selector="edit-field-media-open-button"]'
      // );

      // Check if the element exists and the event listener hasn't been added yet.
      // if (myButton && !myButton.dataset.listenerAttached) {
      //   myButton.addEventListener("mousedown", function (event) {
      //     // Your code to execute on mousedown.
      //     console.log("Button mousedown!");
      //     trackAddMediaClick("cbu04905");
      //   });
      //   myButton.addEventListener("touchstart", function (event) {
      //     // Your code to execute on touchstart.
      //     console.log("Button touchstart!");
      //     trackAddMediaClick("cbu04905");
      //   });
      //   myButton.addEventListener("keydown", function (event) {
      //     // Your code to execute on keydown.
      //     console.log("Button keydown!");
      //     trackAddMediaClick("cbu04905");
      //   });

      //   // Mark the element to prevent re-attaching on subsequent AJAX calls.
      //   myButton.dataset.listenerAttached = true;
      // }

      // @todo FIX THIS - it is not working - the altTextField is not being found.
      // const altTextField = document.querySelector(
      //   "input[data-drupal-selector^='edit-media-0-fields-image-0-alt']"
      // );
      // if (altTextField && !altTextField.dataset.listenerAttached) {
      //   altTextField.addEventListener("change", function () {
      //     trackAltFieldChanged();
      //     console.log("vaGovMedia: alt field change tracked");

      //     altTextField.dataset.listenerAttached = true;
      //   });
      // }
      // Observe the DOM for dynamically-added buttons and attach
      // listeners when they appear. Use a data attribute to ensure we only
      // attach once per element.
      function observeAddMedia(element, selector, eventType, handler) {
        try {
          const root =
            context && typeof context.querySelector === "function"
              ? context
              : document;
          const attachmentFlag = `vaGovMediaAttached${element}${eventType}`;
          const tryAttach = (el) => {
            if (!el || el.dataset[attachmentFlag]) return;
            el.addEventListener(eventType, () => {
              const label =
                el.getAttribute("aria-label") || el.textContent.trim();
              try {
                /* eslint-disable-next-line no-console */
                console.debug(`VaGovMedia: observed ${element} ${eventType}`, label);
              } catch (_e) {
                /* ignore */
              }
              handler();
            });
            el.dataset[attachmentFlag] = "1";
          };

          // Attach to any currently-present nodes.
          Array.from(root.querySelectorAll(selector)).forEach(tryAttach);

          // Observe future nodes and attach when they appear.
          const mo = new MutationObserver((mutations, observer) => {
            for (let i = 0; i < mutations.length; i += 1) {
              const m = mutations[i];
              if (m.addedNodes && m.addedNodes.length) {
                for (let j = 0; j < m.addedNodes.length; j += 1) {
                  const n = m.addedNodes[j];
                  if (n.nodeType === 1) {
                    if (n.matches && n.matches(selector)) {
                      tryAttach(n);
                      observer.disconnect();
                      return;
                    }
                    if (n.querySelectorAll) {
                      Array.from(n.querySelectorAll(selector)).forEach(
                        tryAttach
                      );
                    }
                  }
                }
              }
            }
          });
          mo.observe(root, { childList: true, subtree: true });
        } catch (_e) {
          /* ignore observer setup failures */
        }
      };
      observeAddMedia("AddMediaButton", "input[data-drupal-selector$='field-media-open-button']", "mousedown", trackAddMediaClick);
      observeAddMedia("AddMediaButton", "input[data-drupal-selector$='field-media-open-button']", "touchstart", trackAddMediaClick);
      observeAddMedia("AddMediaButton", "input[data-drupal-selector$='field-media-open-button']", "keydown", trackAddMediaClick);
      observeAddMedia("AltTextField", "input[data-drupal-selector$='edit-media-0-fields-image-0-alt']", "change", trackAltFieldChanged);
      observeAddMedia("AiAltGenerationButton", "button[data-drupal-selector$='edit-media-0-fields-image-0-generate-alt']", "click", trackAiAltGenerationClick);
      observeAddMedia("SubmitButton", "button.js-form-submit.form-submit", "mousedown", trackSubmitClick);
      observeAddMedia("SubmitButton", "button.js-form-submit.form-submit", "touchstart", trackSubmitClick);
      observeAddMedia("SubmitButton", "button.js-form-submit.form-submit", "keydown", trackSubmitClick);
      // End DOM observation code.

    },
  };
})(jQuery, once, Drupal);
