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
        event_action: "file_selected",
      });
    }
  }
  function trackUploadButtonClick(label) {
    if (typeof gtag === "function") {
      gtag("event", "image_upload", {
        event_category: "Media",
        event_label: label || "upload_button",
  event_action: "button_click",
      });
    }
  }
  function trackAddMediaClick(label) {
    console.log("vaGovMedia: trackAddMediaClick called with label:", label);
    if (typeof gtag === "function") {
      gtag("event", "image_upload", {
        event_category: "Media",
        event_label: label || "add_media_button",
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

      // Alt text field delegation (debounced + deduped)
      // Try a few selector forms because Drupal's DOM can vary across widgets.
      const altSelectorCandidates = [
        'input[data-drupal-selector$="edit-media-0-fields-image-0-alt"]',
        'textarea[data-drupal-selector$="edit-media-0-fields-image-0-alt"]',
        '[data-drupal-selector*="image-0-fields-image-0-alt"]',
        'input[name*="image"][name*="alt"]',
      ];
      // Diagnostic: always log how many current matches each selector has so
      // we can see this information even if the delegation guard prevents
      // re-attaching listeners during subsequent behavior invocations.
      try {
        altSelectorCandidates.forEach((s) => {
          const n = document.querySelectorAll(s).length;
          console.log(`[vaGovMedia] alt selector check: "${s}" => ${n} matches`);
        });
      } catch (err) {
        /* ignore query errors */
      }

      if (!document.vaGovMediaAltDelegated) {
        document.vaGovMediaAltDelegated = true;
        const altSelector = altSelectorCandidates.join(',');
        // Use stable keys (data-drupal-selector, name, id) when possible so we
        // deduplicate across DOM replacements. Falls back to per-element Map
        // when no stable identifier exists.
        const lastValMap = new Map();
        const timeoutsMap = new Map();

        function stableKeyFor(field) {
          try {
            if (!field) return null;
            if (field.dataset && field.dataset.drupalSelector) return 'ds:' + field.dataset.drupalSelector;
            if (field.name) return 'name:' + field.name;
            if (field.id) return 'id:' + field.id;
            // If the field is inside a form, use form id/selector plus element index.
            if (field.form) {
              var formId = field.form.id || (field.form.dataset && field.form.dataset.drupalSelector) || null;
              if (formId) {
                var idx = -1;
                if (field.form.elements) {
                  for (var i = 0; i < field.form.elements.length; i++) {
                    if (field.form.elements[i] === field) {
                      idx = i;
                      break;
                    }
                  }
                }
                return 'form:' + formId + ':' + idx;
              }
            }
            return null;
          } catch (err) {
            return null;
          }
        }

        function doTrackAltForKey(field, key) {
          const val = field.value;
          const prev = lastValMap.get(key);
          if (prev === val) return;
          lastValMap.set(key, val);
          console.log('[vaGovMedia] Delegated alt text change tracked for', field, 'key:', key, 'value:', val);
          trackAltFieldChanged();
        }

        function scheduleTrack(field) {
          const key = stableKeyFor(field) || field;
          const val = field.value;
          const prev = lastValMap.get(key);
          // If value didn't change, skip scheduling.
          if (prev === val) return;
          const t = timeoutsMap.get(key);
          if (t) clearTimeout(t);
          const id = setTimeout(function () {
            timeoutsMap.delete(key);
            // If the element was replaced, find the current element using
            // stable selector if available.
            var currentField = field;
            if (typeof key === 'string') {
              try {
                if (key.indexOf('ds:') === 0) {
                  currentField = document.querySelector('[data-drupal-selector="' + key.slice(3) + '"]') || field;
                }
              } catch (err) {
                currentField = field;
              }
            }
            doTrackAltForKey(currentField, key);
          }, 250);
          timeoutsMap.set(key, id);
        }

        document.addEventListener('input', function (e) {
          const field = e.target.closest(altSelector);
          if (!field) return;
          scheduleTrack(field);
        }, true);

        document.addEventListener('change', function (e) {
          const field = e.target.closest(altSelector);
          if (!field) return;
          const key = stableKeyFor(field) || field;
          const t = timeoutsMap.get(key);
          if (t) {
            clearTimeout(t);
            timeoutsMap.delete(key);
          }
          doTrackAltForKey(field, key);
        }, true);
      }

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
      // AI alt regenerate delegated handler: broaden selectors and attach once.
      const aiRegenerateSelectorCandidates = [
        "input[data-drupal-selector$='edit-media-0-fields-image-0-ai-alt-text-generation-0']",
        "button[data-drupal-selector$='edit-media-0-fields-image-0-ai-alt-text-generation-0']",
        "[data-drupal-selector*='ai-alt-text-generation']",
        "button[name*='ai'][name*='alt']",
      ];
      try {
        aiRegenerateSelectorCandidates.forEach((s) => {
          console.log(
            `[vaGovMedia] ai-regenerate selector check: "${s}" => ${document.querySelectorAll(s).length} matches`
          );
        });
      } catch (err) {
        /* ignore */
      }

      if (!document.vaGovMediaAiRegenerateDelegated) {
        document.vaGovMediaAiRegenerateDelegated = true;
        const aiRegenerateSelector = aiRegenerateSelectorCandidates.join(',');

        function delegatedAltTextRegenerateHandler(e) {
          const button = e.target.closest(aiRegenerateSelector);
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
      }

      // Track the last pointer/key activator so we can infer the submitter when
      // e.submitter isn't available (older browsers or programmatic submits).
      if (!document.vaGovMediaLastActivator) {
        document.vaGovMediaLastActivator = { elem: null, ts: 0 };
        document.addEventListener('pointerdown', function (e) {
          document.vaGovMediaLastActivator.elem = e.target;
          document.vaGovMediaLastActivator.ts = Date.now();
        }, true);
        document.addEventListener('keydown', function (e) {
          if (e.key === 'Enter') {
            document.vaGovMediaLastActivator.elem = document.activeElement;
            document.vaGovMediaLastActivator.ts = Date.now();
          }
        }, true);
      }

      // Submit tracking: listen for form submit (fires once per submit)
      if (!document.vaGovMediaSubmitDelegated) {
        document.vaGovMediaSubmitDelegated = true;
        const submitSelectorCandidates = [
          // Exclude AI-regenerate controls (class or data-drupal-selector) explicitly
          "button.js-form-submit.form-submit:not(.ai-alt-text-generation):not([data-drupal-selector*='ai-alt-text-generation'])",
          "input.js-form-submit.form-submit:not(.ai-alt-text-generation):not([data-drupal-selector*='ai-alt-text-generation'])",
          "button[type='submit']:not(.ai-alt-text-generation):not([data-drupal-selector*='ai-alt-text-generation'])",
          "input[type='submit']:not(.ai-alt-text-generation):not([data-drupal-selector*='ai-alt-text-generation'])",
          "button[name*='submit']:not(.ai-alt-text-generation):not([data-drupal-selector*='ai-alt-text-generation'])",
        ];
        try {
          submitSelectorCandidates.forEach((s) => {
            console.log(
              `[vaGovMedia] submit selector check: "${s}" => ${document.querySelectorAll(s).length} matches`
            );
          });
        } catch (err) {
          /* ignore */
        }
        const submitSelector = submitSelectorCandidates.join(',');

        // Short TTL (ms) for a marker we place on the form when we track a
        // submit via delegated click. This prevents double-tracking when both
        // the click and the submit events fire.
        const submitMarkerTTL = 2000;

        // Helper to detect AI-regenerate controls more robustly.
        function isAiRegenerateControl(el) {
          if (!el || el.nodeType !== 1) return false;
          try {
            if (aiRegenerateSelector && (el.matches && el.matches(aiRegenerateSelector))) return true;
            if (el.classList && el.classList.contains('ai-alt-text-generation')) return true;
            var ds = el.getAttribute && el.getAttribute('data-drupal-selector');
            if (ds && ds.indexOf('ai-alt-text-generation') !== -1) return true;
            var name = el.getAttribute && el.getAttribute('name');
            if (name && name.indexOf('ai') !== -1 && name.indexOf('alt') !== -1) return true;
            var val = el.getAttribute && el.getAttribute('value');
            if (val && typeof val === 'string' && val.toLowerCase().indexOf('regenerate alt') !== -1) return true;
          } catch (err) {
            // ignore detection errors
          }
          return false;
        }

        // Delegated click handler on submit candidates. When a user clicks a
        // submit button/input we track immediately (so programmatic submits or
        // early navigation don't lose the event) and mark the form so the
        // subsequent submit handler can skip duplicate tracking.
        function delegatedSubmitClickHandler(e) {
          const button = e.target.closest(submitSelector);
          if (!button) return;
          // Ignore clicks on AI-regenerate controls which may live near submit
          // buttons but are not form submissions.
          if (isAiRegenerateControl(button)) {
            console.log('[vaGovMedia] Click on AI-regenerate control detected; not tracking as submit');
            return;
          }
          // Find the related form: button.form or closest form ancestor.
          const form = button.form || (button.closest && button.closest('form')) || null;

          // If this button is inside a form, mark the form and track as before.
          if (form) {
            try {
              // Mark the form as already tracked just now.
              form.dataset.vaGovMediaSubmitTracked = String(Date.now());
            } catch (err) {
              // ignore dataset write errors
            }
            console.log('[vaGovMedia] Delegated click on submit candidate inside form, tracking submit for form', form, 'button', button);
            trackSubmitClick();
            return;
          }

          // Some dialogs or widgets render submit-like buttons outside a
          // FORM element (for example, modal footer buttons). If the element
          // looks like a submit control (has submit classes), track it too.
          // We still skip AI-regenerate controls above.
          console.log('[vaGovMedia] Delegated click on submit-like control outside form, tracking submit for element', button);
          trackSubmitClick();
        }

        document.addEventListener('click', delegatedSubmitClickHandler, true);

        document.addEventListener('submit', function (e) {
          const form = e.target;
          if (!form || form.nodeName !== 'FORM') return;
          console.log('[vaGovMedia] submit event for form', form, 'e.submitter:', e.submitter);

          // If we recently tracked a delegated click on this form, skip to avoid duplicate events.
          try {
            const v = form.dataset && form.dataset.vaGovMediaSubmitTracked;
            if (v && (Date.now() - Number(v) < submitMarkerTTL)) {
              // clear the marker and skip tracking here since we already tracked on click
              delete form.dataset.vaGovMediaSubmitTracked;
              console.log('[vaGovMedia] submit already tracked via delegated click; skipping duplicate');
              return;
            }
          } catch (err) {
            // ignore dataset read errors and continue to normal flow
          }

          // Prefer the actual e.submitter when available. Otherwise, find
          // submit candidates inside the form and prefer the last matching
          // candidate (likely the visible form submit) while still excluding
          // AI-regenerate controls. Using querySelectorAll + reverse reduces
          // accidental selection of an earlier, unrelated submit input.
          let submitButton = e.submitter || null;
          if (!submitButton) {
            try {
              var candidates = Array.from(form.querySelectorAll(submitSelector));
              if (candidates.length) {
                // prefer the last non-AI candidate, then the last candidate
                submitButton = candidates.slice().reverse().find(function (c) { return !isAiRegenerateControl(c); }) || candidates[candidates.length - 1] || null;
              } else {
                submitButton = null;
              }
            } catch (err) {
              // fallback to original behavior
              try { submitButton = form.querySelector(submitSelector); } catch (e) { submitButton = null; }
            }
          }

          // If the resolved submitButton is actually an AI-regenerate control,
          // try to find an alternative submit candidate in the form. If none
          // exist, skip tracking.
          try {
            if (isAiRegenerateControl(submitButton)) {
              console.log('[vaGovMedia] Resolved submitter is an AI-regenerate control; searching for alternate submit candidate');
              const candidates = Array.from(form.querySelectorAll(submitSelector));
              // prefer a later non-AI candidate
              submitButton = candidates.slice().reverse().find(function (c) { return !isAiRegenerateControl(c); }) || null;
              if (!submitButton) {
                console.log('[vaGovMedia] No non-AI submit candidate found; skipping submit tracking');
                return;
              }
            }
          } catch (err) {
            // ignore matching issues and continue
          }

          if (!submitButton) {
            const last = document.vaGovMediaLastActivator || {};
            if (last.elem && form.contains(last.elem) && Date.now() - last.ts < 2000) {
              submitButton = last.elem;
              console.log('[vaGovMedia] using last activator as submitter', submitButton);
            } else {
              console.log('[vaGovMedia] no submit button found for form submit');
            }
          }

          if (submitButton) {
            console.log('[vaGovMedia] Form submit tracked for', form, 'submitter', submitButton);
            trackSubmitClick();
          }
        }, true);
      }

      // Helper: print the first matched submit candidate's attributes to console.
      // Call from the page console: document.vaGovMediaPrintSubmitCandidate()
      document.vaGovMediaPrintSubmitCandidate = function () {
        try {
          const candidates = (typeof submitSelectorCandidates !== 'undefined') ? submitSelectorCandidates : [
            "button.js-form-submit.form-submit",
            "input.js-form-submit.form-submit",
            "button[type='submit']",
            "input[type='submit']",
            "button[name*='submit']",
          ];
          for (let i = 0; i < candidates.length; i++) {
            const s = candidates[i];
            const el = document.querySelector(s);
            if (el) {
              console.log('[vaGovMedia] First matched submit selector:', s, 'element:', el);
              const attrs = {};
              Array.from(el.attributes).forEach(function (a) { attrs[a.name] = a.value; });
              console.log('[vaGovMedia] element attributes:', attrs);
              return el;
            }
          }
          console.log('[vaGovMedia] No submit candidate matched');
          return null;
        } catch (err) {
          console.error('[vaGovMedia] Error in print helper', err);
          return null;
        }
      };

      // Helper: given an element (or CSS selector or falsy to auto-find by value),
      // print which AI-regenerate and submit selectors match it and which
      // selector would be returned first by querySelector. Call from the page
      // console: document.vaGovMediaWhichSelectorsMatch(elOrSelector)
      document.vaGovMediaWhichSelectorsMatch = function (elOrSelector) {
        try {
          var el = null;
          if (!elOrSelector) {
            // try to find by common button text
            el = Array.from(document.querySelectorAll('input[type="submit"], button[type="submit"]'))
              .find(function (x) { return x.value && x.value.toLowerCase().indexOf('regenerate alt') !== -1; });
          } else if (typeof elOrSelector === 'string') {
            el = document.querySelector(elOrSelector) || document.getElementById(elOrSelector);
          } else {
            el = elOrSelector;
          }

          if (!el) {
            console.log('[vaGovMedia] No element found for', elOrSelector);
            return null;
          }

          console.log('[vaGovMedia] Element to test:', el);

          var aiCandidates = (typeof aiRegenerateSelectorCandidates !== 'undefined') ? aiRegenerateSelectorCandidates : [
            "input[data-drupal-selector$='edit-media-0-fields-image-0-ai-alt-text-generation-0']",
            "button[data-drupal-selector$='edit-media-0-fields-image-0-ai-alt-text-generation-0']",
            "[data-drupal-selector*='ai-alt-text-generation']",
            "button[name*='ai'][name*='alt']",
          ];
          var submitCandidates = (typeof submitSelectorCandidates !== 'undefined') ? submitSelectorCandidates : [
            "button.js-form-submit.form-submit",
            "input.js-form-submit.form-submit",
            "button[type='submit']",
            "input[type='submit']",
            "button[name*='submit']",
          ];

          var matches = { ai: [], submit: [] };

          aiCandidates.forEach(function (s) {
            try { matches.ai.push({ selector: s, matches: !!(el.matches && el.matches(s)), qsReturns: document.querySelector(s) === el }); }
            catch (e) { matches.ai.push({ selector: s, error: e.message }); }
          });
          submitCandidates.forEach(function (s) {
            try { matches.submit.push({ selector: s, matches: !!(el.matches && el.matches(s)), qsReturns: document.querySelector(s) === el }); }
            catch (e) { matches.submit.push({ selector: s, error: e.message }); }
          });

          console.log('[vaGovMedia] AI selector match results:');
          matches.ai.forEach(function (m) { console.log(m.selector, 'matches?', m.matches, 'querySelector returns this el?', m.qsReturns, m.error ? 'error:' + m.error : ''); });
          console.log('[vaGovMedia] Submit selector match results:');
          matches.submit.forEach(function (m) { console.log(m.selector, 'matches?', m.matches, 'querySelector returns this el?', m.qsReturns, m.error ? 'error:' + m.error : ''); });

          function firstMatch(arr) {
            for (var i = 0; i < arr.length; i++) {
              try { if (el.matches && el.matches(arr[i])) return arr[i]; } catch (e) {}
            }
            return null;
          }

          console.log('[vaGovMedia] First AI match:', firstMatch(aiCandidates));
          console.log('[vaGovMedia] First Submit match:', firstMatch(submitCandidates));

          return matches;
        } catch (err) {
          console.error('[vaGovMedia] Error in which-match helper', err);
          return null;
        }
      };
    },
  };
})(jQuery, once, Drupal);
