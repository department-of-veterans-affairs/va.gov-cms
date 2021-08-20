import { Given } from "cypress-cucumber-preprocessor/steps";

Given(`I click the {string} button`, (text) => {
  cy.contains(text).click({ force: true });
});
