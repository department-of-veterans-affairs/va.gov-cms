import { Given } from "@badeball/cypress-cucumber-preprocessor";

Given("I enable the page segment", () => {
  cy.findAllByLabelText("Enable this page segment").check({ force: true });
});

Given("I disable the page segment", () => {
  cy.findAllByLabelText("Enable this page segment").uncheck({ force: true });
});

Given("I enable the page segment within selector {string}", (text) => {
  cy.get(text)
    .findAllByLabelText("Enable this page segment")
    .check({ force: true });
});

Given("I disable the page segment within selector {string}", (text) => {
  cy.get(text)
    .findAllByLabelText("Enable this page segment")
    .uncheck({ force: true });
});
