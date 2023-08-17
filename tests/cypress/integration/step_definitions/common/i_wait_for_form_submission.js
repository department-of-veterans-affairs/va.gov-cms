import { Given } from "cypress-cucumber-preprocessor/steps";

Given("I wait for form submission", () => {
  cy.wait("@formSubmission");
});
