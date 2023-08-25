import { Given } from "@badeball/cypress-cucumber-preprocessor";

Given("I wait until element with selector {string} is visible", (selector) => {
  cy.get(selector).should("be.visible");
});

Given(
  "I wait until element with selector {string} is not visible",
  (selector) => {
    cy.get(selector).should("not.be.visible");
  }
);
