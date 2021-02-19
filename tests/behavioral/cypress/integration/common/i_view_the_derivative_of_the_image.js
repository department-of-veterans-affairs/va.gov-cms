import { Then } from "cypress-cucumber-preprocessor/steps";

Then(`I view the derivative {string} of the image`, (derivative) => {
  cy.window().then((window) => {
    cy.get('@mediaImageUrl').then((url) => {
      const derivativeUrl = url.replace('/files/', `/files/styles/${derivative}/`);
      cy.wrap(derivativeUrl).as('mediaImageDerivativeUrl');
      cy.request(derivativeUrl);
    });
  });
});
