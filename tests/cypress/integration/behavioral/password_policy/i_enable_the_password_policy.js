import { Given } from "cypress-cucumber-preprocessor/steps";
Given('I enable the password policy', () => {
  cy.visit('/admin/config/security/password-policy/va_gov_cms/roles');
  cy.get('#edit-roles-authenticated').check();
  return cy.get('form#password-policy-roles-form').find('input#edit-submit').click();
});
