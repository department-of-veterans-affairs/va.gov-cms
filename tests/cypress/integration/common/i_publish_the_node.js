import { Given } from "cypress-cucumber-preprocessor/steps";
import { faker } from "@faker-js/faker";

Given(`I publish the node`, () => {
  cy.get("@nodeId").then((nid) => {
    cy.visit(`/node/${nid}/edit`);
    cy.scrollToSelector("select#edit-moderation-state-0-state");
    cy.get("select#edit-moderation-state-0-state").select("Published", {
      force: true,
    });
    cy.get(
      "#edit-revision-log-0-value"
    ).type(`[Test revision log]${faker.lorem.sentence()}`, { force: true });
    cy.get("form.node-form").find("input#edit-submit").click();
  });
});
