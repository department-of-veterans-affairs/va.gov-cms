import { Then } from "cypress-cucumber-preprocessor/steps";

Then(`the GTM data layer value for {string} should be set to {string}`, (key, expected) => {
  cy.window().then((window) => {
    const actual = window.drupalSettings.gtm_data[key];
    cy.wrap(actual).should('eq', expected);
  });
});
