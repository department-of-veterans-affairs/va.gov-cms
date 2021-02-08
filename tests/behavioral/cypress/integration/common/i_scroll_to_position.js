import { Then } from "cypress-cucumber-preprocessor/steps";

Then(`I scroll to {string}`, (position) => {
  cy.scrollTo(position);
});
