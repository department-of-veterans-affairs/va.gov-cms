import { Given } from "@badeball/cypress-cucumber-preprocessor";

Given(`I select one item from the {string} Entity Browser modal`, (text) => {
  cy.contains(text).click({ force: true });
  cy.get(".entity-browser-modal iframe").should("exist");
  cy.wait(3000);

  cy.get(".entity-browser-modal iframe")
    .iframe()
    .within(() => {
      cy.get("tr")
        .should("exist")
        .parent()
        .find("[type='checkbox']")
        .first()
        .check({ force: true });
      cy.get("#edit-submit").click({ force: true });
    });
  cy.get(".entity-browser-modal iframe").should("not.exist");
});

Given(
  `I select {int} items from the {string} Entity Browser modal`,
  (numItems, text) => {
    cy.contains(text).click({ force: true });
    cy.get(".entity-browser-modal iframe").should("exist");
    cy.wait(3000);
    cy.get(".entity-browser-modal iframe")
      .iframe()
      .within(() => {
        for (let i = 0; i < numItems; i++) {
          cy.get("input[type='checkbox']")
            .eq(i)
            .should("exist")
            .check({ force: true });
        }
        cy.get("#edit-submit").click({ force: true });
      });
    cy.get(".entity-browser-modal iframe").should("not.exist");
  }
);
