import { faker } from "@faker-js/faker";

Cypress.Commands.add("addMainContentBlockWithRichText", (text) => {
  cy.get("#edit-field-content-block-add-more-browse")
    .scrollIntoView()
    .should("be.visible")
    .click();
  cy.wait(1000);
  cy.get("div#drupal-modal").within(() => {
    cy.wait(1000);
    cy.contains("Rich text")
      .parent()
      .find("input.button")
      .click({ force: true });
  });
  cy.get("div#drupal-modal").should("not.be.visible");
  cy.type_ckeditor("field-wysiwyg-0", text);
});

Cypress.Commands.add("addMainContentBlockWithFile", (type) => {
  cy.get("#edit-field-content-block-add-more-browse")
    .scrollIntoView()
    .should("be.visible")
    .click();
  cy.wait(1000);
  cy.get("div#drupal-modal").within(() => {
    cy.wait(1000);
    cy.contains("Link to file or video")
      .parent()
      .find("input.button")
      .click({ force: true });
  });
  cy.get("div#drupal-modal").should("not.be.visible");
  cy.wait(1000);
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
  cy.get("div#drupal-modal").should("not.be.visible");
  cy.wait(1000);
  cy.findAllByLabelText("Link text").type(
    `[Test Data] ${faker.lorem.sentence()}`,
    { force: true }
  );
  cy.get("form.node-form").find("input#edit-submit").click();
});
