import { Then } from "@badeball/cypress-cucumber-preprocessor";

Then(
  "I should see a confirmation dialog with the text {string}",
  (expectedText) => {
    cy.get("#va-gov-vamc-archive-modal").should("be.visible");
    cy.get("#va-gov-vamc-archive-modal-message").should(
      "contain",
      expectedText,
    );
  },
);
