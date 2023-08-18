import { Given } from "@badeball/cypress-cucumber-preprocessor";

Given("I enable the password policy", () => {
  return cy.drupalDrushCommand([
    "config:set",
    "password_policy.password_policy.va_gov_cms",
    "roles.authenticated",
    "authenticated",
    "--no-interaction",
  ]);
});
