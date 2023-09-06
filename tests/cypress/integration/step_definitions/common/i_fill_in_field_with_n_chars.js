import { Then } from "@badeball/cypress-cucumber-preprocessor";
import { faker } from "@faker-js/faker";

Then(`I fill in {string} with {int} characters`, (label, length) => {
  cy.findAllByLabelText(label).focus();
  cy.findAllByLabelText(label).clear({ force: true });
  cy.findAllByLabelText(label).type(
    faker.lorem.paragraphs(100).substring(0, length),
    { force: true }
  );
  cy.findAllByLabelText(label).blur();
});

Then(
  `I fill in field with selector {string} with {int} characters`,
  (selector, length) => {
    cy.get(selector).focus();
    cy.get(selector).clear({ force: true });
    cy.get(selector).type(faker.lorem.paragraphs(100).substring(0, length), {
      force: true,
    });
    cy.get(selector).blur();
  }
);

Then(
  `I fill in autocomplete field with selector {string} with {int} characters`,
  (selector, length) => {
    cy.get(selector);
    cy.get(selector).focus();
    cy.get(selector).clear({ force: true });
    cy.get(selector).type(faker.lorem.paragraphs(100).substring(0, length), {
      force: true,
    });
  }
);

Then(
  `I fill in ckeditor field {string} with {int} characters`,
  (label, length) => {
    cy.type_ckeditor(label, faker.lorem.paragraphs(100).substring(0, length));
  }
);
