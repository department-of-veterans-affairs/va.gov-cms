import { Given } from "cypress-cucumber-preprocessor/steps";

Given("I save the node", () => {
  cy.get("form.node-form input#edit-submit").click({ force: true });
  cy.window().then((window) => {
    const pagePath = window.location.pathname;
    cy.wrap(pagePath).as('pagePath');
  });
});
