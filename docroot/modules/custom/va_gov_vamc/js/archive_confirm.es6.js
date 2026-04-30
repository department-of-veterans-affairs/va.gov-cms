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
          "form.node-regional-health-care-service-des-edit-form, form.node-regional-health-care-service-des-form"
        ),
        context
      );
      forms.forEach((form) => {
        form.addEventListener("submit", (e) => {
          const moderationState = form.querySelector(
            '[name^="moderation_state"]'
          );
          if (moderationState && moderationState.value === "archived") {
            e.preventDefault();
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

            const focusable = () =>
              Array.from(modal.querySelectorAll("button"));
            const trapFocus = (event) => {
              const f = focusable();
              if (!f.length) return;
              if (event.key === "Tab") {
                const idx = f.indexOf(document.activeElement);
                if (event.shiftKey) {
                  if (idx === 0) {
                    event.preventDefault();
                    f[f.length - 1].focus();
                  }
                } else if (idx === f.length - 1) {
                  event.preventDefault();
                  f[0].focus();
                }
              }
            };

            modal.showModal();
            setTimeout(() => {
              const f = focusable();
              if (f[0]) f[0].focus();
            }, 0);

            const escListener = (event) => {
              if (event.key === "Escape") {
                modal.close("cancel");
              }
            };
            modal.addEventListener("keydown", trapFocus);
            window.addEventListener("keydown", escListener);

            const cancelBtn = modal.querySelector(
              "#va-gov-vamc-archive-cancel"
            );
            const confirmBtn = modal.querySelector(
              "#va-gov-vamc-archive-confirm"
            );
            cancelBtn.onclick = () => modal.close("cancel");
            confirmBtn.onclick = (evt) => {
              evt.preventDefault();
              modal.close("confirm");
            };

            modal.addEventListener(
              "close",
              () => {
                modal.removeEventListener("keydown", trapFocus);
                window.removeEventListener("keydown", escListener);
                if (modal.returnValue === "confirm") {
                  modal.close();
                  form.submit();
                }
                setTimeout(() => {
                  modal.remove();
                }, 100);
              },
              { once: true }
            );
            return false;
          }
        });
      });
    },
  };
})(Drupal, window.once, window.drupalSettings);
