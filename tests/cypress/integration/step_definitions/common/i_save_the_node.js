import { Given } from "@badeball/cypress-cucumber-preprocessor";

Given("I save the node", () => {
  cy.get("form.node-form input#edit-submit").click({ force: true });
  cy.window().then((window) => {
    const pagePath = window.location.pathname;
    cy.wrap(pagePath).as("pagePath");
  });
});

Given(
  "I am prevented from saving the node by {string} {string} with error {string}",
  (fieldType, selector, html5Error) => {
    cy.get("form.node-form input#edit-submit").click({ force: true });
    cy.get((fieldType += ":invalid")).should("have.length", 1);
    cy.get(selector).then(($input) => {
      expect($input[0].validationMessage).to.eq(html5Error);
    });
  }
);
