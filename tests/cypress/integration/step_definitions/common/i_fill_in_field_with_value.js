import { Then } from "@badeball/cypress-cucumber-preprocessor";
import { faker } from "@faker-js/faker";

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

Then("I fill in {string} field with fake text", (label) => {
  cy.findAllByLabelText(label).type(faker.lorem.sentence(), { force: true });
});

Then("I fill in field with selector {string} with fake text", (selector) => {
  cy.get(selector).focus();
  cy.get(selector).type(faker.lorem.sentence(), { force: true });
});

Then("I fill in {string} field with fake link", (label) => {
  cy.findAllByLabelText(label).type(faker.internet.url(), { force: true });
});
