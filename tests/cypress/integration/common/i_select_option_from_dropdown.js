import { Given } from "cypress-cucumber-preprocessor/steps";

Given(`I select option {string} from dropdown {string}`, (option, label) => {
  cy.findAllByLabelText(label).select(option, { force: true });
});

Given(
  `I select option {string} from dropdown with selector {string}`,
  (option, selector) => {
    cy.get(selector).select(option, { force: true });
  }
);
