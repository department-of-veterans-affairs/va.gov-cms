import { Given } from "cypress-cucumber-preprocessor/steps";

Given(`I initiate a content release from the command line`, () => {
  cy.visit('/admin/content/deploy');
  cy.drupalDrushCommand('va-gov-build-frontend');
});
