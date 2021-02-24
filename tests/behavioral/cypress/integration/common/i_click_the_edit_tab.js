import { Given } from "cypress-cucumber-preprocessor/steps";

Given("I click the edit tab", () => {
  cy.get(".tabs__tab a").contains("Edit").click({ force: true });
});
