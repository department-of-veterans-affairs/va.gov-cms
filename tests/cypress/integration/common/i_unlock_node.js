import { Then } from "cypress-cucumber-preprocessor/steps";

Then(`I unlock node {int}`, (nid) => {
  return cy.drupalUnlockNode(nid);
});
