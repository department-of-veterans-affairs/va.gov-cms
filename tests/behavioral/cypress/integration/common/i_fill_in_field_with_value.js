import { Then } from "cypress-cucumber-preprocessor/steps";

Then(`I fill in {string} with {string}`, (label, value) => {
  cy.findAllByLabelText(label).type(value);
});
