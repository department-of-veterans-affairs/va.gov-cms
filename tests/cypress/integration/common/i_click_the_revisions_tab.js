import { Given } from "cypress-cucumber-preprocessor/steps";

Given("I click the revisions tab", () => {
  cy.scrollTo("top", { ensureScrollable: false });
  cy.get(".tabs__tab a").contains("Revisions").click({ force: true });
});
