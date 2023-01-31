import { Given } from "cypress-cucumber-preprocessor/steps";
// Moved to 'i_should_see as part of #11609 work; this can be deleted

Given('an option with the text {string} from dropdown with selector {string} should be visible', (text, selector) => {
  cy.get(`${selector} option:not([class*="hidden-option"])`).should('contain.text', text);
});

Given('an option with the text {string} from dropdown with selector {string} should not be visible', (text, selector) => {
  cy.get(`${selector} option:not([class*="hidden-option"])`).should('not.contain.text', text);
});
