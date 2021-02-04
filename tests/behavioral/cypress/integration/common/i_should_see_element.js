import { Then } from "cypress-cucumber-preprocessor/steps";

Then(`I should see element {string}`, (text) => {
  cy.get(text).should('exist');
});
