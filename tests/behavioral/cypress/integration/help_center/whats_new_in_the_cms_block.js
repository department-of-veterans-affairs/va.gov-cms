import { Then } from 'cypress-cucumber-preprocessor/steps';

Then(`I should see the what's new in the CMS block`, () => cy.findByText(`What's new in the CMS`).should('exist'));
Then(`I should not see the what's new in the CMS block`, () => cy.findByText(`What's new in the CMS`).should('not.exist'));
