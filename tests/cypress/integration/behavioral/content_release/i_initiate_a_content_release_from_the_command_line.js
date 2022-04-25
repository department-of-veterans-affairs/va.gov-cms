import { Given } from "cypress-cucumber-preprocessor/steps";

Given(`I initiate a content release from the command line with the branch {string}`, (branchName) => {
  cy.visit('/admin/content/deploy');
  cy.drupalDrushCommand(`va-gov-build-frontend "$(git rev-parse ${branchName})"`);
});
