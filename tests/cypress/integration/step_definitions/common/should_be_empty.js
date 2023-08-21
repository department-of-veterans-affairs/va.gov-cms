import { Then } from "@badeball/cypress-cucumber-preprocessor";

Then("an element with the selector {string} should be empty", (selector) =>
  cy.get(selector).should("be.empty")
);
Then("an element with the selector {string} should not be empty", (selector) =>
  cy.get(selector).should("not.be.empty")
);
