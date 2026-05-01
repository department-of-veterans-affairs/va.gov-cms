/* eslint-disable max-nested-callbacks */
import { Then } from "@badeball/cypress-cucumber-preprocessor";

Then(
  `the {string} derivative of the media image should match the fixture {string}`,
  (derivative, fixture) => {
    cy.get("@mediaImageUrl").then((url) => {
      const derivativeUrl = url.replace(
        "/files/",
        `/files/styles/${derivative}/public/`,
      );
      cy.wrap(derivativeUrl).as("mediaImageDerivativeUrl");
      cy.request({ url: derivativeUrl, encoding: "base64" }).then(
        (response) => {
          const derivativeBase64 = response.body;
          cy.fixture(fixture, "base64").then((fixtureBase64) => {
            cy.task("pixelmatchCompare", {
              derivativeBase64,
              fixtureBase64,
              testTitle: Cypress.currentTest.title,
            }).then(({ differences, width, height }) => {
              // For right now, let's say that no more than 5% of pixels can be different.
              const threshold = width * height * 0.05;
              cy.wrap(differences).should("be.lessThan", threshold);
            });
          });
        },
      );
    });
  },
);
