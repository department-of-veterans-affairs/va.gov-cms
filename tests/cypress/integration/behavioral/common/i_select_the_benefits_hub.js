import { Given } from "cypress-cucumber-preprocessor/steps";

Given(`I select the {string} benefits hub`, (text) => {
  cy.contains("Select Benefit Hub(s)").click({ force: true });
  cy.get(".entity-browser-modal iframe").should("exist");
  cy.wait(3000);

  cy.get(".entity-browser-modal iframe").iframe().within(() => {
    cy.get("tr")
      .contains(text)
      .should("exist")
      .parent()
      .find("[type='checkbox']")
      .check({ force: true });
    cy.get("#edit-submit").click({ force: true });
  });
  cy.get(".entity-browser-modal iframe").should("not.exist");
});
