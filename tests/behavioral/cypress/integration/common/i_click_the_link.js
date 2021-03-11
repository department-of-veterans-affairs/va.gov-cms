import { Given } from "cypress-cucumber-preprocessor/steps";

Given(`I click the {string} link`, (text) => {
  cy.contains(text).click({ force: true });
});
