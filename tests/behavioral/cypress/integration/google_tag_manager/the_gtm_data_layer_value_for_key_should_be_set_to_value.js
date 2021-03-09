import { Then } from "cypress-cucumber-preprocessor/steps";

Then(`the GTM data layer value for {string} should be set to {string}`, (key, expected) => {
  cy.getDataLayer()
    .then((dataLayer) => cy.wrap(dataLayer[key]))
    .should('eq', expected);
});
