import { Given } from "cypress-cucumber-preprocessor/steps";

Given(`the {string} checkbox should be checked`, (text) => {
  cy.contains(text).parent().find("input").should("be.checked");
});

Given(`the {string} checkbox should not be checked`, (text) => {
  cy.contains(text).parent().find("input").should("not.be.checked");
});
