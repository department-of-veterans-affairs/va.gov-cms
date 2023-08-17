import { Given } from "@badeball/cypress-cucumber-preprocessor";

Given(`I attempt to visit {string}`, (url) => {
  cy.visit(url, {
    failOnStatusCode: false,
  });
});
