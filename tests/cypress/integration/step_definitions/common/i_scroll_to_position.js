import { Then } from "@badeball/cypress-cucumber-preprocessor";

Then(`I scroll to position {string}`, (position) => {
  cy.scrollTo(position);
});
