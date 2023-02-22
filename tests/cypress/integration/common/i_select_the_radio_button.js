import { Given } from "cypress-cucumber-preprocessor/steps";

Given(`I select the {string} radio button`, (text) => {
  cy.contains(text).parent().find("input").check({ force: true });
});
