import { Then } from "cypress-cucumber-preprocessor/steps";

Then(`I clear field {string}`, (label, value) => {
  cy.findAllByLabelText(label).clear({ force: true });
});
