import { Then } from "cypress-cucumber-preprocessor/steps";

Then("an element with the selector {string} should be empty", (selector) =>
  cy.get(selector).should("be.empty")
);
Then("an element with the selector {string} should not be empty", (selector) =>
  cy.get(selector).should("not.be.empty")
);
