import { Given } from "@badeball/cypress-cucumber-preprocessor";

Given(`I visit the term`, () => {
  cy.get("@termId").then((tid) => cy.visit(`/taxonomy/term/${tid}`));
});

Given(`I edit the term`, () => {
  cy.get("@termId").then((tid) => cy.visit(`/taxonomy/term/${tid}/edit`));
});
