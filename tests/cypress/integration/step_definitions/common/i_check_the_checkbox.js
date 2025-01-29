import { Given } from "@badeball/cypress-cucumber-preprocessor";

Given(`I check the {string} checkbox`, (text) => {
  cy.contains(text).parent().find("input").check({ force: true });
});

Given(`I uncheck the {string} checkbox`, (text) => {
  cy.contains(text).parent().find("input").uncheck({ force: true });
});

Given(`I check the {string} checkbox within {string}`, (text, selector) => {
  cy.get(selector).contains(text).parent().find("input").check({ force: true });
});

Given(`I uncheck the {string} checkbox within {string}`, (text, selector) => {
  cy.get(selector)
    .contains(text)
    .parent()
    .find("input")
    .uncheck({ force: true });
});

Given(`I check the checkbox with selector {string}`, (selector) => {
  cy.get(selector).check({ force: true });
});

Given(`I uncheck the checkbox with selector {string}`, (selector) => {
  cy.get(selector).uncheck({ force: true });
});

Given(`I check all checkboxes within {string}`, (selector) => {
  cy.get(selector).find('input[type="checkbox"]').as("checkboxes").check();

  cy.get("@checkboxes").each((checkbox) => {
    expect(checkbox[0].checked).to.equal(true);
  });
});

Given(`I check the first checkbox within {string}`, (selector) => {
  cy.get(selector).find('input[type="checkbox"]').first().check();
});

Given(`I uncheck the first checkbox within {string}`, (selector) => {
  cy.get(selector).find('input[type="checkbox"]').first().uncheck();
});
