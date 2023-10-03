import { Then } from "@badeball/cypress-cucumber-preprocessor";

Then(
  `the URL for the link with text {string} should contain {string}`,
  (linkText, urlText) => {
    cy.get(`a[href*="${urlText}"]:contains("${linkText}")`).should("exist");
  }
);
