import { Then } from "cypress-cucumber-preprocessor/steps";

Then(`I fill in {string} with {string}`, (label, value) => {
  cy.findAllByLabelText(label)
    .focus()
    .clear({ force: true })
    .type(value, { force: true })
    .blur();
});

Then(`I fill in field with selector {string} with value {string}`, (selector, value) => {
  cy.get(selector)
    .focus()
    .clear({ force: true })
    .type(value, { force: true })
    .blur();
});
