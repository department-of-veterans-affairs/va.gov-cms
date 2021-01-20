import { Then } from "cypress-cucumber-preprocessor/steps";

Then(`the URL for the link with text {string} should contain {string}`, (linkText, urlText) => {
  cy.get(`a:contains("${linkText}")`)
    .should('have.attr', 'href')
    .and('include', urlText);
});
