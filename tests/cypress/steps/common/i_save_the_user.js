import { Given } from "cypress-cucumber-preprocessor/steps";

Given("I save the user", () => {
  cy.get("form.user-form input#edit-submit").click({ force: true });
});
