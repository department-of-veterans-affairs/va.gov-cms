import { Then } from "cypress-cucumber-preprocessor/steps";
const pixelmatch = require('pixelmatch');
const { PNG } = require('pngjs');
const { expect } = require('chai');

Then(`the {string} derivative of the media image should match the fixture {string}`, (derivative, fixture) => {
  cy.window().then((window) => {
    cy.get('@mediaImageUrl').then((url) => {
      const derivativeUrl = url.replace('/files/', `/files/styles/${derivative}/public/`);
      cy.wrap(derivativeUrl).as('mediaImageDerivativeUrl');
      cy.request({ url: derivativeUrl, encoding: 'binary' }).then((response) => {
        const derivativeImage = PNG.sync.read(new Buffer(response.body, 'binary'));
        const width = derivativeImage.width;
        const height = derivativeImage.height;
        cy.fixture(fixture, 'binary').then((fixtureBody) => {
          const fixtureImage = PNG.sync.read(new Buffer(fixtureBody, 'binary'));
          const diff = new PNG({width, height});
          const differences = pixelmatch(fixtureImage.data, derivativeImage.data, diff.data, width, height);
          const diffData = PNG.sync.write(diff).toString('binary');
          const path =
            `cypress/screenshots/pixelmatch_diffs/${cy.state('ctx').test.title}.png`;
          cy.writeFile(path, diffData, 'binary');
          // For right now, let's say that no more than 5% of pixels can be different.
          const threshold = width * height * .05;
          cy.wrap(differences).should('be.lessThan', threshold);
        });
      });
    });
  });
});
