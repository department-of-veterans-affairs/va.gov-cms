import { Then } from "cypress-cucumber-preprocessor/steps";

Then(`I should not see the JSD widget`, () => {
  cy.get('iframe#jsd-widget').should('not.exist');
});
