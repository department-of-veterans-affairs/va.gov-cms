import { Then } from "@badeball/cypress-cucumber-preprocessor";

Then(
  "the element with selector {string} should contain {string}",
  (selector, string) => {
    cy.get(selector).should("contain", string);
  }
);

Then(
  "the element with selector {string} should not contain {string}",
  (selector, string) => {
    cy.get(selector).should("not.contain", string);
  }
);
