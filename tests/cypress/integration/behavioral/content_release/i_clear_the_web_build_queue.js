import { Given } from "cypress-cucumber-preprocessor/steps";

Given(`I clear the web build queue`, () => {
  cy.drupalDrushCommand('va-gov-build-frontend-empty-queue');
});
