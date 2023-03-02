/**
 * @file
 */

((Drupal) => {
  const wysiwygSetter = (e) => {
    const statusId = e.target.value;
    // When the COVID status radio button is changed,
    // change the COVID Details field to the appropriate COVID status term description.
    if (
      document.querySelector(
        "#cke_edit-field-supplemental-status-more-i-0-value iframe"
      )
    ) {
      const iframeDocument = document.querySelector(
        "#cke_edit-field-supplemental-status-more-i-0-value iframe"
      ).contentDocument;
      if (iframeDocument.body) {
        iframeDocument.body.innerHTML = `<div>${drupalSettings.vamcCovidStatusTermText[statusId].description}</div>`;
      }
    }
  };

  Drupal.behaviors.vaGovSetCovidTermText = {
    attach() {
      // Use the supplemental status to drive the details content.
      const supplementalStatusChoices = document.querySelectorAll(
        ".form-item--field-supplemental-status [id^='edit-field-supplemental-status-']"
      );
      // When user clicks, populate the status.
      supplementalStatusChoices.forEach((choice) => {
        document
          .getElementById(choice.id)
          .addEventListener("click", wysiwygSetter);
      });
    },
  };
})(Drupal);
