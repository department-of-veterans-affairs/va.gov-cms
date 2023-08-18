import { Then } from "@badeball/cypress-cucumber-preprocessor";

Then(`the path should contain {string}`, (string) => {
  cy.location("pathname").should("contain", string);
});

Then(`the path should equal {string}`, (string) => {
  cy.location("pathname").should("eq", string);
});
