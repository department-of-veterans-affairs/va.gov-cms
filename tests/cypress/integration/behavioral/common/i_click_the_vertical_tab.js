import { Given } from "cypress-cucumber-preprocessor/steps";

Given("I click the {string} vertical tab", (text) => {
  cy.get(".vertical-tabs a").contains(text).click({ force: true });
});
