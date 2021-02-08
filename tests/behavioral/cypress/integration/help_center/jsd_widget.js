import { Then } from 'cypress-cucumber-preprocessor/steps';

Then(/I should see(?: (a|an|the))? JSD widget/, () => cy.get('iframe#jsd-widget').iframe().find('p').should('exist'));
Then(/I should not see(?: (a|an|the))? JSD widget/, () => cy.get('iframe#jsd-widget').should('not.exist'));
