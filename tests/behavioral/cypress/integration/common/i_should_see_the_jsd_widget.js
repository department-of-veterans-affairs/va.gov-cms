import { Then } from "cypress-cucumber-preprocessor/steps";

Then(`I should see the JSD widget`, () => {
  cy.get('iframe#jsd-widget')
    .iframe()
    .find('p')
    .should('exist');
});
