import { Given } from "cypress-cucumber-preprocessor/steps";

Given(`I visit the node`, () => {
  cy.get('@nodeId').then((nid) => cy.visit(`/node/${nid}`));
});

Given(`I edit the node`, () => {
  cy.get('@nodeId').then((nid) => cy.visit(`/node/${nid}/edit`));
});

Given(`I view the moderation history for the node`, () => {
  cy.get('@nodeId').then((nid) => cy.visit(`/node/${nid}/moderation-history`));
});
