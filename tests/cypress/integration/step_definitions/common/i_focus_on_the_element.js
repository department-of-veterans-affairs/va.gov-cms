import { When } from "@badeball/cypress-cucumber-preprocessor";

When(`I focus on the element with selector {string}`, (element) => {
  cy.get(element).focus();
});

When(`I focus on the element with xpath {string}`, (xpath) => {
  cy.xpath(xpath).focus();
});
