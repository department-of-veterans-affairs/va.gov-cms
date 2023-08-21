import { Given } from "@badeball/cypress-cucumber-preprocessor";

Given(`the {string} radio button should be selected`, (text) => {
  cy.contains(text).parent().find("input").should("be.checked");
});
