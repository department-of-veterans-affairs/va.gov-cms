import { When } from "@badeball/cypress-cucumber-preprocessor";

When(`I visit {string}`, (url) => {
  cy.visit(url);
});
