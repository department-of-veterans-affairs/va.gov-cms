import { Given } from "cypress-cucumber-preprocessor/steps";

Given(`I click the {string} button`, (text) => {
  cy.contains("input", text).click({ force: true });
});

Given(`I click the button with selector {string}`, (selector) => {
  cy.get(selector).click({ force: true });
});
