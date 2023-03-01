import { Given } from "cypress-cucumber-preprocessor/steps";

Given(`I check the {string} checkbox`, (text) => {
  cy.contains(text).parent().find("input").check({ force: true });
});

Given(`I uncheck the {string} checkbox`, (text) => {
  cy.contains(text).parent().find("input").uncheck({ force: true });
});
