import { Given } from "@badeball/cypress-cucumber-preprocessor";

Given(`I select option {string} from dropdown {string}`, (option, label) => {
  cy.findAllByLabelText(label).select(option, { force: true });
});

Given(
  `I select option {string} from dropdown with selector {string}`,
  (option, selector) => {
    cy.get(selector).select(option, { force: true });
  }
);
