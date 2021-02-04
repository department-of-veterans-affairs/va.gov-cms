import { Then } from "cypress-cucumber-preprocessor/steps";

Then(`I should see the JSD widget`, () => {
  cy.get('iframe#jsd-widget').should('exist');
  cy.contains('contact help desk').should('exist');
  cy.get(`a:contains("help desk")`)
    .should('have.attr', 'href')
    .and('include', 'https://va-gov.atlassian.net');
});
