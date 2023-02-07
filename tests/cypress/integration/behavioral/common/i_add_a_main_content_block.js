import { Given, Then } from "cypress-cucumber-preprocessor/steps";
import { faker } from "@faker-js/faker";

Given(`I add a main content block with a link to a {string} file`, (type) => {
  cy.get("#edit-field-content-block-add-more-browse")
    .scrollIntoView()
    .should("be.visible")
    .click();
  cy.get("div#drupal-modal").within(() => {
    cy.contains("Link to file or video")
      .parent()
      .find("input.button")
      .click({ force: true });
  });
  cy.get("div#drupal-modal").should("not.exist");
  cy.get("div.page-wrapper").contains("Add media").click();
  cy.get("div#drupal-modal").within(() => {
    cy.contains(type).click({ force: true });
    cy.wait(2000);
    cy.get(".media-library-views-form__rows")
      .children()
      .first()
      .children()
      .first()
      .find('input[type="checkbox"]')
      .check({ force: true });
  });
  cy.get("button.media-library-select").contains("Insert selected").click();
  cy.get("div#drupal-modal").should("not.exist");
  cy.findAllByLabelText(
    "Link text"
  ).type(`[Test Data] ${faker.lorem.sentence()}`, { force: true });
  cy.get("form.node-form").find("input#edit-submit").click();
});
