import { Given } from "cypress-cucumber-preprocessor/steps";

Given(`I check the {string} checkbox`, (text) => {
  cy.contains(text).parent().find("input").check({ force: true });
});

Given(`I uncheck the {string} checkbox`, (text) => {
  cy.contains(text).parent().find("input").uncheck({ force: true });
});

Given(`I check the checkbox with selector {string}`, (selector) => {
  cy.get(selector).check({ force: true });
});

Given(`I uncheck the checkbox with selector {string}`, (selector) => {
  cy.get(selector).uncheck({ force: true });
});
