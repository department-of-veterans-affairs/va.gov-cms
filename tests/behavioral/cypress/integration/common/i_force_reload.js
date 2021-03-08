import { Then } from "cypress-cucumber-preprocessor/steps";

Then(`I force reload`, () => {
  cy.reload(true);
});
