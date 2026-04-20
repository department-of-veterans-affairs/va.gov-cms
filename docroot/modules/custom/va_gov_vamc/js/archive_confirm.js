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
          // Find the moderation state select.
          const moderationState = form.querySelector(
            '[name^="moderation_state"]'
          );
          if (moderationState && moderationState.value === "archived") {
            const confirmed = window.confirm(settings.message);
            if (!confirmed) {
              e.preventDefault();
              e.stopImmediatePropagation();
              return false;
            }
          }
        });
      });
    },
  };
})(Drupal, window.once, window.drupalSettings);
