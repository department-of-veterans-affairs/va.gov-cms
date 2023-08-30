import { Given } from "@badeball/cypress-cucumber-preprocessor";

Given("I enable the page segment", () => {
  cy.findAllByLabelText("Enable this page segment").check({ force: true });
});
