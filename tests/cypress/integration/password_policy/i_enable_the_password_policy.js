import { Given } from "cypress-cucumber-preprocessor/steps";

Given("I enable the password policy", () => {
  return cy.drupalDrushCommand([
    "config:set",
    "password_policy.password_policy.va_gov_cms",
    "roles.authenticated",
    "authenticated",
    "--no-interaction",
  ]);
});
