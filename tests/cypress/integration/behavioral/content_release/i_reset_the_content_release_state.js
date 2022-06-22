import { Given } from "cypress-cucumber-preprocessor/steps";

Given(`I reset the content release state from the command line`, () => {
  cy.drupalDrushCommand('va-gov-content-release-reset-state');
});
