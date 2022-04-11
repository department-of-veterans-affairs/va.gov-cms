import { Given } from "cypress-cucumber-preprocessor/steps";

Given(`I visit the node`, () => {
  cy.get('@nodeId').then((nid) => cy.visit(`/node/${nid}`));
});

Given(`I edit the node`, () => {
  cy.get('@nodeId').then((nid) => cy.visit(`/node/${nid}/edit`));
});
