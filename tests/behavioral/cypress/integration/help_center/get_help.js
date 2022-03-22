import { Then } from 'cypress-cucumber-preprocessor/steps';

Then(/I should see(?: (a|an|the))? Get Help link/, () => cy.get('.suffix-links').should('exist'));
