import { Given } from "@badeball/cypress-cucumber-preprocessor";

Given("I feature the content", () => {
  cy.get("#edit-field-featured-value").check({ force: true });
});
