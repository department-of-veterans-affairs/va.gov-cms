import { Then } from 'cypress-cucumber-preprocessor/steps';

Then('I should see an element with the selector {string}', (selector) => cy.get(selector).should('be.visible'));
Then('I should not see an element with the selector {string}', (selector) => cy.get(selector).should('not.be.visible'));

Then(`I should see {string}`, (text) => cy.contains(text).should('be.visible'));
Then(`I should not see {string}`, (text) => cy.contains(text).should('not.be.visible'));

Then('I should see an element with the xpath {string}', (expression) => cy.xpath(expression).should('be.visible'));
Then('I should not see an element with the xpath {string}', (expression) => cy.xpath(expression).should('not.be.visible'));

Then('I should see xpath {string}', (expression) => cy.xpath(expression).should('be.visible'));
Then('I should not see xpath {string}', (expression) => cy.xpath(expression).should('not.be.visible'));

Given('I should see an option with the text {string} from dropdown with selector {string}', (text, selector) => {
  cy.get(`${selector} option:not([class*="hidden-option"])`).should('contain.text', text);
});

Given('I should not see an option with the text {string} from dropdown with selector {string}', (text, selector) => {
  cy.get(`${selector} option:not([class*="hidden-option"])`).should('not.contain.text', text);
});

Then('I should see an image with the selector {string}', (selector) => {
  cy.get(selector)
    .should('be.visible')
    .and(($img) => {
      expect($img[0].naturalWidth).to.be.greaterThan(0);
      expect($img[0].naturalHeight).to.be.greaterThan(0);
    });
});
