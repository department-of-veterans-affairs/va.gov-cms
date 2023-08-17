import { Then } from "cypress-cucumber-preprocessor/steps";

Then(`I scroll to element {string}`, (element) => {
  cy.scrollToSelector(element);
});

Then(`I scroll to xpath {string}`, (xpath) => {
  cy.scrollToXpath(xpath);
});
