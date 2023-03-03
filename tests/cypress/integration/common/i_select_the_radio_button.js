import { Given } from "cypress-cucumber-preprocessor/steps";

Given(`I select the {string} radio button`, (text) => {
  cy.contains(text).parent().find("input").check({ force: true });
});

Given(`I select the radio button with the value {string}`, (value) => {
  cy.get('[type="radio"]').check(value);
});
