import { Given } from "@badeball/cypress-cucumber-preprocessor";

Given(`I initiate a content release from the command line`, () => {
  cy.drupalDrushCommand("va-gov-content-release-request-frontend-build");
});
