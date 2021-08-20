import { Then } from "cypress-cucumber-preprocessor/steps";

Then(`I scroll to element {string}`, (element) => {
  cy.scrollToSelector(element);
});
