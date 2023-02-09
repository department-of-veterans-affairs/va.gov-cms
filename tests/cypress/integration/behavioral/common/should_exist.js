import { Then } from 'cypress-cucumber-preprocessor/steps';

Then('an element with the selector {string} should exist', (selector) => cy.get(selector).should('exist'));
Then('an element with the selector {string} should not exist', (selector) => cy.get(selector).should('not.exist'));

Then(`{string} should exist`, (text) => cy.contains(text).should('exist'));
Then(`{string} should not exist`, (text) => cy.contains(text).should('not.exist'));

Then('an element with the xpath {string} should exist', (expression) => cy.xpath(expression).should('exist'));
Then('an element with the xpath {string} should not exist', (expression) => cy.xpath(expression).should('not.exist'));

Then('xpath {string} should exist', (expression) => cy.xpath(expression).should('exist'));
Then('xpath {string} should not exist', (expression) => cy.xpath(expression).should('not.exist'));

Then('an image with the selector {string} should exist', (selector) => {
  cy.get(selector)
    .should('exist')
    .and(($img) => {
      expect($img[0].naturalWidth).to.be.greaterThan(0);
      expect($img[0].naturalHeight).to.be.greaterThan(0);
    });
});
