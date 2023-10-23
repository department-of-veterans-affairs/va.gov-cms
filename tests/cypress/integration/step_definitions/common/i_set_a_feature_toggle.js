import { When } from "@badeball/cypress-cucumber-preprocessor";

When(
  "I set the {string} feature toggle to {string}",
  (featureToggleMachineName, value) => {
    const label = String.prototype.toUpperCase(featureToggleMachineName);
    // Set the feature toggle to the value of "value".
    let setValue = 0;
    if (value === "on") {
      setValue = 1;
    }
    return cy.setAFeatureToggle(featureToggleMachineName, label, setValue);
  }
);
