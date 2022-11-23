import { Then } from 'cypress-cucumber-preprocessor/steps';

Then('the element with selector {string} should be empty', (selector) => cy.get(selector).should('be.empty'));
Then('the element with selector {string} should not be empty', (selector) => cy.get(selector).should('not.be.empty'));
