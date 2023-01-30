/**
 * @file
 */

((Drupal) => {
  let statusId;
  const textSetter = () => {
    const covidStatusValue = document.querySelectorAll(
      ".form-item--field-supplemental-status input"
    );

    covidStatusValue.forEach((element) => {
      if (element.checked) {
        statusId = element.value;
      }
    });
    // If there's nothing in the COVID Details field
    // or when the COVID status radio button is changed,
    // change the COVID Details field to the appropriate COVID status term description
    let iframeDocument = "";
    if (
      document.querySelector(
        "#cke_edit-field-supplemental-status-more-i-0-value iframe"
      )
    ) {
      iframeDocument = document.querySelector(
        "#cke_edit-field-supplemental-status-more-i-0-value iframe"
      ).contentDocument;
      iframeDocument.body.innerHTML = `<p>
        ${drupalSettings.vamcCovidStatusTermText[statusId].name}</p>
        ${drupalSettings.vamcCovidStatusTermText[statusId].description}`;
    }
  };

  Drupal.behaviors.vaGovSetCovidTermText = {
    attach() {
      // Let's set the text on page load, and whenever radios are clicked.
      window.addEventListener("DOMContentLoaded", textSetter);
      const supplementalStatusChoices = document.querySelectorAll(
        ".form-item--field-supplemental-status [id^='edit-field-supplemental-status-']"
      );
      // check if an interval has already been set up
      supplementalStatusChoices.forEach((choice) => {
        document
          .getElementById(choice.id)
          .addEventListener("click", textSetter);
      });
    },
  };
})(Drupal);
