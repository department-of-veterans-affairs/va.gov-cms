import { When } from "@badeball/cypress-cucumber-preprocessor";

When(`I click {string}`, (text) => {
  cy.contains(text).click();
});
