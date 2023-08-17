import { Given } from "@badeball/cypress-cucumber-preprocessor";

Given(
  "the option {string} from dropdown {string} should be selected",
  (option, label) => {
    cy.findAllByLabelText(label).select(option, { force: true });
  }
);

Given(
  "the option {string} from dropdown with selector {string} should be selected",
  (option, selector) => {
    cy.get(`${selector} option:selected`).should("have.text", option);
  }
);
