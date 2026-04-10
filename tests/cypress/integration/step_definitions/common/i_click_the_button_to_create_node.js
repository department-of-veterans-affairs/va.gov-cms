import { Given } from "@badeball/cypress-cucumber-preprocessor";

Given(`I click the button to create node and continue`, () => {
  cy.contains("input", "Save and continue editing").click({
    force: true,
  });
  cy.location("pathname", { timeout: 10000 }).should(
    "not.include",
    "/node/add"
  );
  cy.injectAxe();
  cy.checkAccessibility();
  cy.getDrupalSettings().then((drupalSettings) => {
    const { node } = drupalSettings;
    const nodeId = node.id;
    cy.wrap(nodeId).as("nodeId");
    cy.trackEntity("nodes", nodeId, { type: node.contentType });
    cy.window().then((window) => {
      const pagePath = window.location.pathname;
      cy.wrap(pagePath).as("pagePath");
    });
  });
});

Given(`I click the button to create node`, () => {
  cy.get("input#edit-submit").click({
    force: true,
  });
  cy.location("pathname", { timeout: 10000 }).should(
    "not.include",
    "/node/add"
  );
  cy.injectAxe();
  cy.checkAccessibility();
  cy.getDrupalSettings().then((drupalSettings) => {
    const { node } = drupalSettings;
    const nodeId = node.id;
    cy.wrap(nodeId).as("nodeId");
    cy.trackEntity("nodes", nodeId, { type: node.contentType });
    cy.window().then((window) => {
      const pagePath = window.location.pathname;
      cy.wrap(pagePath).as("pagePath");
    });
  });
});
