import { Then } from "cypress-cucumber-preprocessor/steps";

Then(`the GTM data layer value for {string} should be unset`, (key) => {
  cy.getDataLayer()
    .then((dataLayer) => cy.wrap(dataLayer[key]))
    .should('not.exist');
});
