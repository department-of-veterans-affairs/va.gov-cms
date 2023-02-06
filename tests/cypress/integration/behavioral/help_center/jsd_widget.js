import { Then } from 'cypress-cucumber-preprocessor/steps';

Then(/(?: (a|an|the))? JSD widget should exist/, () => cy.get('iframe#jsd-widget').iframe().should('exist'));
Then(/(?: (a|an|the))? JSD widget should not exist/, () => cy.get('iframe#jsd-widget').should('not.exist'));
