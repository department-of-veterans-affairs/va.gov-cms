import { Given } from "cypress-cucumber-preprocessor/steps";

Given(`I attempt to visit {string}`, (url) => {
  cy.visit(url, {
    failOnStatusCode: false,
  });
});
