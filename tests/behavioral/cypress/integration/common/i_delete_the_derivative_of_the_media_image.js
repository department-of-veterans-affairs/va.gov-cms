import { Then } from "cypress-cucumber-preprocessor/steps";

Then(`I delete the {string} derivative of the media image`, (derivative) => {
  cy.window().then((window) => {
    cy.get('@mediaImageUrl').then((url) => {
      const derivativeUrl = url.replace('/files/', `/files/styles/${derivative}/public/`);
      const path = (new URL(derivativeUrl)).pathname;
      cy.wrap(derivativeUrl).as('mediaImageDerivativeUrl');
      cy.drupalDrushEval('echo \Drupal::service("file_system")->realpath("public://");', (result) => {
        const publicPath = result.stdout;
        cy.exec(`rm "${path.replace('/sites/default/files', publicPath)}"`);
      });
    });
  });
});
