import { Then } from "cypress-cucumber-preprocessor/steps";

Then(`the path should contain {string}`, (string) => {
  cy.location("pathname").should("contain", string);
});

Then(`the path should equal {string}`, (string) => {
  cy.location("pathname").should("eq", string);
});
