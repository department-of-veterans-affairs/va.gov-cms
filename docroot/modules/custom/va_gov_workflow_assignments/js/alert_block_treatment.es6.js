/**
 * @file
 */

((Drupal) => {
  Drupal.behaviors.vaGovAlertBlockTreatment = {
    attach() {
      // Alert type toggle.
      const alertType = document.getElementById(
        "edit-field-is-this-a-header-alert-"
      );
      // Holds the scope field.
      const scopeContainer = document.getElementById(
        "edit-field-node-reference-wrapper"
      );
      alertType.addEventListener("change", () => {
        // Toggle scope visibility.
        scopeContainer.style.display = "block";
        if (alertType.value !== "banner_alert") {
          scopeContainer.style.display = "none";
        }
      });
      window.addEventListener("DOMContentLoaded", () => {
        // Toggle scope visibility.
        scopeContainer.style.display = "block";
        if (alertType.value !== "banner_alert") {
          scopeContainer.style.display = "none";
        }
      });
    },
  };
})(Drupal);
