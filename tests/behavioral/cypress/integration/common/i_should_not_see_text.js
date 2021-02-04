import { Then } from "cypress-cucumber-preprocessor/steps";

Then(`I should not see {string}`, (text) => {
  cy.contains(text).should('not.exist');
});
