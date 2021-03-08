import { Then } from "cypress-cucumber-preprocessor/steps";

Then(`the GTM data layer value for {string} should be set`, (key) => {
  cy.getDataLayer().then((dataLayer) => {
    const actual = dataLayer[key];
    cy.wrap(actual).should('exist');
  });
});
