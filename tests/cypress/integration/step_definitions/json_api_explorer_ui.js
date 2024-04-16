import { Then, When } from '@badeball/cypress-cucumber-preprocessor';

Then("only one JSON:API Explorer link should be visible", () => {
  cy.get('#block-vagovclaro-content table tbody tr').should('have.length', 1);
  cy.get('#block-vagovclaro-content table tbody tr td').should('contain', 'VA.gov JSON:API');
});

Then("the JSON:API Explorer tag sections should be collapsed by default and expandable", () => {
  cy.get(".opblock-tag-section.is-open").should("have.length", 0);
  cy.get(".opblock-tag-section h3").first().click();
  cy.get(".opblock-tag-section.is-open").should("have.length", 1);
  cy.get(".opblock-tag-section h3").first().click();
  cy.get(".opblock-tag-section.is-open").should("have.length", 0);
});

Then("only the {string} tag should be visible", (tag) => {
  cy.get(".opblock-tag-section").should("have.length", 1);
  cy.get(".opblock-tag-section h3").contains(tag);
});

When(`I click try it out for the {string} endpoint of the {string} tag`, (endpoint, tag) => {
  // Need to convert spaces to underscores for tag CSS selector.
  tag = tag.replace(/ /g, '_');

  cy.get(`#operations-${tag}-${endpoint} button.try-out__btn`).click();
});

Then(`the live response status code for the {string} endpoint of the {string} tag should be {string}`, (endpoint, tag, status) => {
  // Need to convert spaces to underscores for tag CSS selector.
  tag = tag.replace(/ /g, '_');

  cy.get(`#operations-${tag}-${endpoint} .live-responses-table .response-col_status`).should('contain', status);
});
