import { Then } from 'cypress-cucumber-preprocessor/steps';

Then('the element with selector {string} should contain {string}', (selector, string) => {
  cy.get(selector).should('contain', string)
});

Then('the element with selector {string} should not contain {string}', (selector, string) => {
  cy.get(selector).should('not.contain', string);
});
