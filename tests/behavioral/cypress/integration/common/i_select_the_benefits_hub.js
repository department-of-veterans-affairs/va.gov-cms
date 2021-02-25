import { Given } from "cypress-cucumber-preprocessor/steps";

Given(`I select the {string} benefits hub`, (text) => {
  cy.contains("Select Benefit Hub(s)").click({ force: true });
  cy.get(".entity-browser-modal iframe").should("exist");
  cy.wait(2000);

  cy.get(".entity-browser-modal iframe").iframe().within(() => {
    cy.get("tr").contains(text).should("exist");
    cy.get("tr")
      .contains(text)
      .parent()
      .find("[type='checkbox']")
      .check({ force: true });
    cy.get("#edit-submit").click({ force: true });
  });
});
