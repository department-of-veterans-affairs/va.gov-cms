((Drupal, once, drupalSettings) => {
  Drupal.behaviors.vaGovVamcArchiveConfirm = {
    attach: (context) => {
      const settings =
        drupalSettings.va_gov_vamc && drupalSettings.va_gov_vamc.archiveConfirm;
      if (!settings || !settings.facilityCount || !settings.message) {
        return;
      }
      const forms = once(
        "va-gov-vamc-archive-confirm",
        document.querySelectorAll(
          [
            "form.node-health-care-local-health-service-edit-form",
            "form.node-regional-health-care-service-des-edit-form",
          ].join(", ")
        ),
        context
      );
      forms.forEach((form) => {
        const submitBtn = form.querySelector(
          'input.form-submit#edit-submit[value="Save"]'
        );
        if (!submitBtn) {
          return;
        }

        // Remove any previous event listener to avoid duplicate handlers.
        const handleClick = (e) => {
          e.preventDefault();
          e.stopImmediatePropagation();

          // Create a modal dialog using <dialog>.
          let modal = document.getElementById("va-gov-vamc-archive-modal");
          if (!modal) {
            modal = document.createElement("dialog");
            modal.id = "va-gov-vamc-archive-modal";
            modal.setAttribute("aria-modal", "true");
            modal.setAttribute("role", "dialog");
            modal.innerHTML = `
              <form method="dialog" class="va-gov-vamc-archive-modal-form">
                <div id="va-gov-vamc-archive-modal-message"></div>
                <div class="va-gov-vamc-archive-modal-actions">
                  <button value="cancel" type="button" id="va-gov-vamc-archive-cancel">${Drupal.t(
                    "Cancel"
                  )}</button>
                  <button value="confirm" type="submit" id="va-gov-vamc-archive-confirm">${Drupal.t(
                    "Confirm"
                  )}</button>
                </div>
              </form>`;
            document.body.appendChild(modal);
          }
          modal.querySelector(
            "#va-gov-vamc-archive-modal-message"
          ).textContent = settings.message;

          // Focus management and accessibility.
          setTimeout(() => {
            const f = modal.querySelector("button");
            if (f) f.focus();
          }, 0);

          // Cancel button closes the modal.
          modal.querySelector("#va-gov-vamc-archive-cancel").onclick = () => {
            modal.close("cancel");
          };
          // Confirm button submits the form.
          modal.querySelector("#va-gov-vamc-archive-confirm").onclick = (
            evt
          ) => {
            evt.preventDefault();
            modal.close("confirm");
          };

          modal.addEventListener(
            "close",
            () => {
              if (modal.returnValue === "confirm") {
                // Remove this event listener so the next click is native.
                submitBtn.removeEventListener("click", handleClick);
                // Trigger a native click event to proceed with submission.
                submitBtn.click();
              }
              setTimeout(() => {
                modal.remove();
              }, 100);
            },
            { once: true }
          );

          modal.showModal();
          return false;
        };
        // Remove any previous event listener to avoid duplicate handlers.
        submitBtn.addEventListener("click", handleClick);
      });
    },
  };
})(Drupal, window.once, window.drupalSettings);
