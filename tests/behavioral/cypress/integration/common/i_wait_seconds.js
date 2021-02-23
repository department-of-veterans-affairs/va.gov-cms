import { Given } from "cypress-cucumber-preprocessor/steps";

Given(`I wait {string} seconds`, (seconds) => {
  cy.wait(seconds * 1000);
});
