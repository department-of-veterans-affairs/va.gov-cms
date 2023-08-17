import { Given } from "@badeball/cypress-cucumber-preprocessor";

Given(
  `I should receive status code {int} when I request {string}`,
  (status, url) => {
    cy.request({
      url,
      followRedirect: false,
      failOnStatusCode: false,
    }).then((response) => {
      expect(response.status).to.equal(status);
    });
  }
);
