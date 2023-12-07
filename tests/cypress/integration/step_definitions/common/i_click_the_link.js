import { Given } from "@badeball/cypress-cucumber-preprocessor";

Given(`I click the {string} link`, (text) => {
  cy.contains(text).invoke("removeAttr", "target").click({ force: true });
});

Given(`I click the link with xpath {string}`, (xpath) => {
  cy.xpath(xpath).click({ force: true });
});

Given(
  `I click the link with xpath {string} containing {string}`,
  (xpath, text) => {
    cy.xpath(xpath).contains(text).click({ force: true });
  }
);

Given(`I really click the link with selector {string}`, (selector) => {
  cy.get(selector).realClick();
});

Given(`I really click the link with xpath {string}`, (xpath) => {
  return cy.xpath(xpath).realClick({ force: true });
});
