import { Given } from "cypress-cucumber-preprocessor/steps";

Given('the option {string} from dropdown {string} should be selected', (option, label) => {
  cy.findAllByLabelText(label).select(option, { force: true });
});

Given('the option {string} from dropdown with selector {string} should be selected', (option, selector) => {
  cy.get(`${selector} option:selected`).should('have.text', option);
});

Given('an option with the text {string} from dropdown with selector {string} should be selectable', (text, selector) => {
  cy.get(`${selector} option:not([class*="hidden-option"])`).should('contain.text', text);
});

Given('an option with the text {string} from dropdown with selector {string} should not be selectable', (text, selector) => {
  cy.get(`${selector} option:not([class*="hidden-option"])`).should('not.contain.text', text);
});
