import { Then } from "cypress-cucumber-preprocessor/steps";

Then(`I should not see element {string}`, (text) => {
  cy.get(text).should('not.exist');
});
