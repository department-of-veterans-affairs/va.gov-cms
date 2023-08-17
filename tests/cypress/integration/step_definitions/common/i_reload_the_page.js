import { Then } from "@badeball/cypress-cucumber-preprocessor";

Then(`I reload the page`, () => {
  cy.reload();
});
