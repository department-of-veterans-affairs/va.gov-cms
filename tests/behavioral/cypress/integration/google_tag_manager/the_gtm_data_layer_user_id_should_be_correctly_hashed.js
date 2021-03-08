import { Then } from "cypress-cucumber-preprocessor/steps";

Then(`the GTM data layer user id should be correctly hashed`, () => {
  cy.get('@uid')
    .should('exist')
    .then((uid) => cy.drupalDrushEval(`echo \\Drupal\\Component\\Utility\\Crypt::hashBase64((string)${uid});`))
    .then((result) => result.stdout)
    .then((expected) => {
      cy.getDataLayer()
        .then((dataLayer) => cy.wrap(dataLayer.userId))
        .should('eq', expected);
    });
});
