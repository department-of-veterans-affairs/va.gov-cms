import { Then } from "@badeball/cypress-cucumber-preprocessor";

Then(`I unlock node {int}`, (nid) => {
  return cy.drupalUnlockNode(nid);
});
