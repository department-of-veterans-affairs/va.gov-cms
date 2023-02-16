import { Then } from "cypress-cucumber-preprocessor/steps";

Then(`I reload the page`, () => {
  cy.reload();
});
