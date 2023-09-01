import { Then } from "@badeball/cypress-cucumber-preprocessor";

Then("the Facility Locator API ID field should not be editable", () => {
  cy.get(
    "#locations-and-contact-information .node__content > .not-editable, .node__content > #locations-and-contact-information .not-editable"
  ).should("exist");
  cy.get(
    '[data-drupal-selector="edit-field-facility-locator-api-id-0-value"]'
  ).should("not.exist");
});
