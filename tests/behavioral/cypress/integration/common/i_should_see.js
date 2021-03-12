import { Then } from 'cypress-cucumber-preprocessor/steps';

Then('I should see an element with the selector {string}', (text) => cy.get(text).should('exist'));
Then('I should not see an element with the selector {string}', (text) => cy.get(text).should('not.exist'));

Then(`I should see {string}`, (text) => cy.contains(text).should('exist'));
Then(`I should not see {string}`, (text) => cy.contains(text).should('not.exist'));
