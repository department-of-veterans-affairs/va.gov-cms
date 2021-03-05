import { Then } from "cypress-cucumber-preprocessor/steps";

Then(`the GTM data layer value for {string} should be unset`, (key) => {
  cy.window().then((window) => {
    const actual = window.drupalSettings.gtm_data[key];
    cy.wrap(actual).should('eq', undefined);
  });
});
