import { Then } from 'cypress-cucumber-preprocessor/steps';

Then(/I should see(?: (a|an|the))? Get Help link/, () => cy.get('#get-help-button').should('exist'));
