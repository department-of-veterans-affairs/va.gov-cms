import { Given } from "cypress-cucumber-preprocessor/steps";

Given(`I initiate a content release from the command line with the branch {string}`, (branchName) => {
  cy.drupalDrushCommand(`va-gov-content-release-request-frontend-build "$(git rev-parse ${branchName})"`);
});
