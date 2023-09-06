import { Given } from "@badeball/cypress-cucumber-preprocessor";

Given("I wait for form submission", () => {
  cy.wait("@formSubmission");
});
