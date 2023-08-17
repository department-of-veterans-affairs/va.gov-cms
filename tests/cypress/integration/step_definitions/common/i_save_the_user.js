import { Given } from "@badeball/cypress-cucumber-preprocessor";

Given("I save the user", () => {
  cy.get("form.user-form input#edit-submit").click({ force: true });
});
