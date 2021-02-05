import { Then } from "cypress-cucumber-preprocessor/steps";

Then(`I should not see the what's new in the CMS block`, () => {
  cy.get('#block-whatsnewinthecms').should('not.exist');
});
