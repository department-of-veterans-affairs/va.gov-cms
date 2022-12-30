/**
 * @file
 */

((Drupal) => {
  let statusId;
  const textSetter = () => {
    // The targeted tooltip fieldset.
    const fieldset = document.getElementById(
      "covid-safety-guidelines-status-text"
    );

    // Remove the previous setting = sanity reset.
    if (document.getElementById("covid-safety-guidelines-status-text-target")) {
      document
        .getElementById("covid-safety-guidelines-status-text-target")
        .remove();
      document
        .getElementById("covid-safety-guidelines-status-text-prefix")
        .remove();
    }

    const covidStatusValue = document.querySelectorAll(
      ".form-item--field-supplemental-status input"
    );

    covidStatusValue.forEach((element) => {
      if (element.checked) {
        statusId = element.value;
      }
    });


      // The text that is placed in the covid tooltip status box.
      // const covidStatusTextDiv = document.createElement("div");
      // covidStatusTextDiv.id = "covid-safety-guidelines-status-text-target";
      // covidStatusTextDiv.innerHTML =
      //   drupalSettings.vamcCovidStatusTermText[statusId].name +
      //   drupalSettings.vamcCovidStatusTermText[statusId].description;
      // fieldset.append(covidStatusTextDiv);
      iframeDocument = document.querySelector("iframe").contentDocument;
      iframeDocument.body.innerHTML =
        drupalSettings.vamcCovidStatusTermText[statusId].name +
        drupalSettings.vamcCovidStatusTermText[statusId].description;


      // Covid guidelines legend and description.
      // const covidStatusTextDivPrefix = document.createElement("div");
      // covidStatusTextDivPrefix.id =
      //   "covid-safety-guidelines-status-text-prefix";
      // covidStatusTextDivPrefix.innerHTML =
      //   '<h5>Guidelines</h5><div class="fieldset__description">Site visitors will see the following message for the level you selected.</div>';
      // fieldset.before(covidStatusTextDivPrefix);

  };

  Drupal.behaviors.vaGovSetCovidTermText = {
    attach() {
      // Let's set the text on page load, and whenever radios are clicked.
      window.addEventListener("DOMContentLoaded", textSetter);
      const supplemental_status_choices = document.querySelectorAll("[id^='edit-field-supplemental-status-']");
        // check if an interval has already been set up
        supplemental_status_choices.forEach(choice => {
          document
          .getElementById(choice.id)
          .addEventListener("click", textSetter);
      });
  // TODO: fix the null issue
  /* set_covid_term_text.js?rnkeh2:35 Uncaught TypeError: Cannot read properties of null (reading 'contentDocument')
    at textSetter (set_covid_term_text.js?rnkeh2:35:56)
    */

    },
  };
})(Drupal);
