import { Given } from "cypress-cucumber-preprocessor/steps";

Given(`the {string} radio button should be selected`, (text) => {
  cy.contains(text).parent().find("input").should("be.checked");
});
