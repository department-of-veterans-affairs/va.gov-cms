import { Given } from "@badeball/cypress-cucumber-preprocessor";

Given(
  `I reset the content release frontend version from the command line`,
  () => {
    cy.drupalDrushCommand("va-gov-content-release-reset-frontend-version");
  }
);
