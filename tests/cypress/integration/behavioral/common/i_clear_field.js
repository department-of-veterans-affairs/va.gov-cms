import { Then } from "cypress-cucumber-preprocessor/steps";

Then(`I clear field {string}`, (label) => {
  cy.findAllByLabelText(label).clear({ force: true });
});
