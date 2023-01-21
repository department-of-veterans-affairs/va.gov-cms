import { Then } from 'cypress-cucumber-preprocessor/steps';

Then('I should ssee an element with the selector {string}', (selector) => cy.get(selector).should('be.visible'));
Then('I should not ssee an element with the selector {string}', (selector) => cy.get(selector).should('not.be.visible'));

Then('I should ssee {string}', (text) => cy.get('div.page-wrapper').contains(text).should('be.visible'));
Then('I should not ssee {string}', (text) => cy.get('div.page-wrapper').contains(text).should('not.be.visible'));

Then('I should ssee an element with the xpath {string}', (expression) => cy.xpath(expression).should('be.visible'));
Then('I should not ssee an element with the xpath {string}', (expression) => cy.xpath(expression).should('not.be.visible'));

Then('I should ssee xpath {string}', (expression) => cy.xpath(expression).should('be.visible'));
Then('I should not ssee xpath {string}', (expression) => cy.xpath(expression).should('not.be.visible'));

Given('I should ssee an option with the text {string} from dropdown with selector {string}', (text, selector) => {
  cy.get(`${selector} option:not([class*="hidden-option"])`).should('contain.text', text);
});

Given('I should not ssee an option with the text {string} from dropdown with selector {string}', (text, selector) => {
  cy.get(`${selector} option:not([class*="hidden-option"])`).should('not.contain.text', text);
});

Then('I should ssee an image with the selector {string}', (selector) => {
  cy.get(selector)
    .should('be.visible')
    .and(($img) => {
      expect($img[0].naturalWidth).to.be.greaterThan(0);
      expect($img[0].naturalHeight).to.be.greaterThan(0);
    });
});
