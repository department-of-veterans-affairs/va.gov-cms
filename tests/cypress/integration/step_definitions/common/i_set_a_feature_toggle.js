import { When } from "@badeball/cypress-cucumber-preprocessor";

When("I set the {string} feature toggle to {string}", (featureToggleMachineName, value) => {
  let featureToggleAdminUrl = '/admin/config/system/feature_toggle';
  cy.visit(featureToggleAdminUrl);
  // check or uncheck the box based on the value of "value".
  if (value === "on") {
    cy.get(`input[name=${featureToggleMachineName}]`).check({force: true});
  }
  if (value === "off") {
    cy.get(`input[name=${featureToggleMachineName}]`).uncheck({force: true});
  }
  cy.get('#edit-submit').click();
});
