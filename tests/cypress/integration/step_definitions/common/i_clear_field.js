import { Then } from "@badeball/cypress-cucumber-preprocessor";

Then(`I clear field {string}`, (label) => {
  cy.findAllByLabelText(label).clear({ force: true });
});
