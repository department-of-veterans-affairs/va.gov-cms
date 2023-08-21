import { Given } from "@badeball/cypress-cucumber-preprocessor";

Given(`I reset the content release state from the command line`, () => {
  cy.drupalDrushCommand("va-gov-content-release-reset-state");
});
