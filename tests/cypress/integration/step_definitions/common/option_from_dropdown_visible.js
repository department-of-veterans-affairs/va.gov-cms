import { Given } from "@badeball/cypress-cucumber-preprocessor";

Given(
  "an option with the text {string} from dropdown with selector {string} should be visible",
  (text, selector) => {
    cy.get(`${selector} option:not([class*="hidden-option"])`).should(
      "contain.text",
      text
    );
  }
);

Given(
  "an option with the text {string} from dropdown with selector {string} should not be visible",
  (text, selector) => {
    cy.get(`${selector} option:not([class*="hidden-option"])`).should(
      "not.contain.text",
      text
    );
  }
);
