import { Given } from "cypress-cucumber-preprocessor/steps";

Given(`I check accessibility`, () => {
  cy.checkAccessibility();
});
