import { Then } from "@badeball/cypress-cucumber-preprocessor";

Then("an element with the selector {string} should be disabled", (selector) =>
  cy.get(selector).should("be.disabled")
);
Then(
  "an element with the selector {string} should not be disabled",
  (selector) => cy.get(selector).should("not.be.disabled")
);
