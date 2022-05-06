import { Given } from "cypress-cucumber-preprocessor/steps";

Given(`I click the {string} link`, (text) => {
  cy.contains(text).invoke('removeAttr', 'target').click({ force: true });
});

Given(`I click the link with xpath {string} containing {string}`, (xpath, text) => {
  cy.xpath(xpath).contains(text).click({ force: true });
});
