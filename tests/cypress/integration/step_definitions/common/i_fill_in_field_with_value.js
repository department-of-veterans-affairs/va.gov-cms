import { Then } from "@badeball/cypress-cucumber-preprocessor";

Then(`I fill in {string} with {string}`, (label, value) => {
  cy.findByLabelText(label).focus();
  cy.findByLabelText(label).clear({ force: true });
  cy.findByLabelText(label).type(value, { force: true });
  cy.findByLabelText(label).blur();
});

Then(
  `I fill in field with selector {string} with value {string}`,
  (selector, value) => {
    cy.get(selector).focus();
    cy.get(selector).clear({ force: true });
    cy.get(selector).type(value, { force: true });
    cy.get(selector).blur();
  }
);

Then(
  `I fill in autocomplete field with selector {string} with value {string}`,
  (selector, value) => {
    cy.get(selector).focus();
    cy.get(selector).clear({ force: true });
    cy.get(selector).type(value, { force: true });
  }
);
