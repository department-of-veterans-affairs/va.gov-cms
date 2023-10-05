/* eslint-disable max-nested-callbacks */
import { Given } from "@badeball/cypress-cucumber-preprocessor";
import { faker } from "@faker-js/faker";

const creators = {
  event: () => {
    // cy.findAllByLabelText("Name").type(
    //   `[Test Data] ${faker.lorem.sentence()}`,
    //   { force: true }
    // );
    // cy.get(
    //   "#edit-field-datetime-range-timezone-0-time-wrapper-value-date"
    // ).type("2023-11-04", { force: true });
    // cy.get(
    //   "#edit-field-datetime-range-timezone-0-time-wrapper-value-date"
    // ).type("2023-11-04", { force: true });
    // cy.get(
    //   "#edit-field-datetime-range-timezone-0-time-wrapper-value-time"
    // ).type("10:00:00", { force: true });
    // cy.get(
    //   "#edit-field-datetime-range-timezone-0-time-wrapper-end-value-time"
    // ).type("11:00:00", { force: true });
    // cy.get("#edit-field-datetime-range-timezone-0-timezone").select("Phoenix");
    // cy.get("#edit-field-datetime-range-timezone-0-make-recurring").check();
    // cy.get("#edit-field-datetime-range-timezone-0-interval").type("1");
    // cy.get("#edit-field-datetime-range-timezone-0-repeat-end-date").type(
    //   "2023-11-07",
    //   { force: true }
    // );
    // cy.get("#edit-field-datetime-range-timezone-0-repeat").select("DAILY");
    // cy.findAllByLabelText("Where should the event be listed?").select(
    //   "VA Alaska health care: Events",
    //   { force: true }
    // );
    // cy.findAllByLabelText("Street address").type(
    //   faker.address.streetAddress(),
    //   { force: true }
    // );
    // cy.findAllByLabelText("City").type(faker.address.city(), { force: true });
    // cy.findAllByLabelText("State").select("Alabama", { force: true });
    // cy.findAllByLabelText("Section").select("--Outreach Hub", { force: true });
    // cy.scrollToSelector("#edit-field-media-open-button");
    // cy.get("#edit-field-media-open-button").click({ force: true });
    // cy.get(".dropzone", {
    //   timeout: 20000,
    // }).should("exist");
    // cy.get(".dropzone").attachFile("images/polygon_image.png", {
    //   subjectType: "drag-n-drop",
    // });
    // cy.findAllByLabelText("Alternative text").type(faker.lorem.sentence(), {
    //   force: true,
    // });
    // cy.get('[data-drupal-selector="edit-media-0-fields-field-owner"]').select(
    //   "VACO",
    //   { force: true }
    // );
    // cy.get("#edit-revision-log-0-value").type(
    //   `[Test revision log 1]${faker.lorem.sentence()}`,
    //   { force: true }
    // );
    // cy.get("button").contains("Save and insert").click({ force: true });
    // cy.get(
    //   'div.media-library-item[data-drupal-selector="edit-field-media-selection-0"]',
    //   {
    //     timeout: 20000,
    //   }
    // ).should("exist");
    cy.get("form.node-form").find("input#edit-submit").click();
    cy.get(".node__content").contains("Sun, Nov 5 2023, 10:00am - 11:00am MST");
    cy.scrollTo("top", { ensureScrollable: false });
    cy.get(".tabs__tab a").contains("Edit").click({ force: true });
    cy.get("#edit-field-datetime-range-timezone-0-manage-instances").click();
    cy.get("table#manage-instances")
      .find(".dropbutton-action")
      .first()
      .find("a")
      .click({ force: true });
    cy.get("#manage-instances form").find("input.form-submit").click();
    cy.get("#manage-instances form").should("not.exist");
    cy.get("button.ui-dialog-titlebar-close").click();
    return cy.wait(1000);
  },
};

Given("{string} node should be created successfully", (contentType) => {
  const creator = creators[contentType];
  assert.isNotNull(
    creator,
    `I do not know how to save ${contentType} nodes yet.  Please add a definition in ${__filename}.`
  );
  cy.visit(`/node/add/${contentType}`);
  cy.injectAxe();
  cy.scrollTo("top");
  cy.checkAccessibility();
  creator().then(() => {
    cy.get("#edit-revision-log-0-value").type(
      `[Test revision log]${faker.lorem.sentence()}`,
      { force: true }
    );
    cy.get("form.node-form").find("input#edit-submit").click();
    cy.location("pathname", { timeout: 10000 }).should(
      "not.include",
      "/node/add"
    );
    cy.injectAxe();
    cy.checkAccessibility();
    cy.drupalWatchdogHasNoNewErrors();
    cy.getDrupalSettings().then((drupalSettings) => {
      const { currentPath } = drupalSettings.path;
      cy.wrap(currentPath.split("/").pop()).as("nodeId");
      cy.window().then((window) => {
        const pagePath = window.location.pathname;
        cy.wrap(pagePath).as("pagePath");
      });
    });
  });
});
