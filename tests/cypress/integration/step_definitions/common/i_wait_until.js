import { Then } from "@badeball/cypress-cucumber-preprocessor";

Then("I wait until element with selector {string} is visible", (selector) => {
  cy.get(selector).should("be.visible");
});

Then(
  "I wait until element with selector {string} is not visible",
  (selector) => {
    cy.get(selector).should("not.be.visible");
  }
);

Then("I wait until I see {string}", (text) => {
  cy.get(text).should("be.visible");
});
