import { Given } from "cypress-cucumber-preprocessor/steps";
Given('I disable the password policy', () => {
  return cy.drupalDrushCommand([
    'config:set',
    'password_policy.password_policy.va_gov_cms',
    'roles.content_admin',
    '0',
    '--no-interaction',
  ]);
});
