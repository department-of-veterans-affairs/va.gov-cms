import { Given } from "@badeball/cypress-cucumber-preprocessor";

Given(`I wait {string} seconds`, (seconds) => {
  cy.wait(seconds * 1000);
});
