import { Given } from "cypress-cucumber-preprocessor/steps";
Given('I disable the password policy', () => {
  cy.visit('/admin/config/security/password-policy/va_gov_cms/roles');
  cy.get('[type="checkbox"]').uncheck();
  return cy.get('form#password-policy-roles-form').find('input#edit-submit').click();
});
