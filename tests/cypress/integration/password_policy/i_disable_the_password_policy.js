import { Given } from "cypress-cucumber-preprocessor/steps";

Given("I disable the password policy", () => {
  return cy.drupalDrushCommand([
    "config:set",
    "password_policy.password_policy.va_gov_cms",
    "roles.authenticated",
    "0",
    "--no-interaction",
  ]);
});
