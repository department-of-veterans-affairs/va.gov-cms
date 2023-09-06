import { Given } from "@badeball/cypress-cucumber-preprocessor";

Given(`I set the status of the node to {string}`, (status) => {
  cy.get("@nodeId").then((nid) => {
    cy.visit(`/node/${nid}/edit`);
    cy.scrollToSelector("select#edit-moderation-state-0-state");
    cy.get("select#edit-moderation-state-0-state").select(status, {
      force: true,
    });
    cy.get("form.node-form").find("input#edit-submit").click();
  });
});
