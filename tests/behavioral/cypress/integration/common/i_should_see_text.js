import { Then } from "cypress-cucumber-preprocessor/steps";

Then(`I should see {string}`, (text) => {
  cy.contains(text);
});
