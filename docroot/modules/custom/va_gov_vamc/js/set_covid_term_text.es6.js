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

    // We don't want the tooltip if a status isn't set.
    fieldset.style.display = "none";

    if (statusId) {
      fieldset.style.display = "block";

      // The text that is placed in the covid tooltip status box.
      const covidStatusTextDiv = document.createElement("div");
      covidStatusTextDiv.id = "covid-safety-guidelines-status-text-target";
      covidStatusTextDiv.innerHTML =
        drupalSettings.vamcCovidStatusTermText[statusId].name +
        drupalSettings.vamcCovidStatusTermText[statusId].description;
      fieldset.append(covidStatusTextDiv);

      // Covid guidelines legend and description.
      const covidStatusTextDivPrefix = document.createElement("div");
      covidStatusTextDivPrefix.id =
        "covid-safety-guidelines-status-text-prefix";
      covidStatusTextDivPrefix.innerHTML =
        '<h5>Guidelines</h5><div class="fieldset__description">Site visitors will see the following message for the level you selected.</div>';
      fieldset.before(covidStatusTextDivPrefix);
    }
  };

  Drupal.behaviors.vaGovSetCovidTermText = {
    attach() {
      // Let's set the text on page load, and whenever radios are clicked.
      window.addEventListener("DOMContentLoaded", textSetter);
      document
        .getElementById("group-covid-19-safety-guidelines")
        .addEventListener("click", textSetter);
    },
  };
})(Drupal);
