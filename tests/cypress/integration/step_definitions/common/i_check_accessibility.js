import { Given } from "@badeball/cypress-cucumber-preprocessor";

Given(`I check accessibility`, () => {
  cy.checkAccessibility();
});
