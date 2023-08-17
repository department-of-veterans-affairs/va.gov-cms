import { Given } from "@badeball/cypress-cucumber-preprocessor";

Given("I click the {string} vertical tab", (text) => {
  cy.get(".vertical-tabs a").contains(text).click({ force: true });
});
