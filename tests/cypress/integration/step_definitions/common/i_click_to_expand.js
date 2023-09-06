import { Given } from "@badeball/cypress-cucumber-preprocessor";

Given(`I click to expand {string}`, (text) => {
  cy.contains("summary", text).click({ force: true });
});

Given(`I click to expand the item with selector {string}`, (selector) => {
  cy.get(selector).click({ force: true });
});
