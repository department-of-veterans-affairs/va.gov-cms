import { Then } from "cypress-cucumber-preprocessor/steps";

Then("an element with the selector {string} should be visible", (selector) =>
  cy.get(selector).should("be.visible")
);
Then(
  "an element with the selector {string} should not be visible",
  (selector) => cy.get(selector).should("not.be.visible")
);

Then("{string} should be visible", (text) =>
  cy.get("div.page-wrapper").contains(text).should("be.visible")
);
Then("{string} should not be visible", (text) =>
  cy.get("div.page-wrapper").contains(text).should("not.be.visible")
);

Then("an element with the xpath {string} should be visible", (expression) =>
  cy.xpath(expression).should("be.visible")
);
Then("an element with the xpath {string} should not be visible", (expression) =>
  cy.xpath(expression).should("not.be.visible")
);

Then("xpath {string} should be visible", (expression) =>
  cy.xpath(expression).should("be.visible")
);
Then("xpath {string} should not be visible", (expression) =>
  cy.xpath(expression).should("not.be.visible")
);

Then("an image with the selector {string} should be visible", (selector) => {
  cy.get(selector)
    .should("be.visible")
    .and(($img) => {
      expect($img[0].naturalWidth).to.be.greaterThan(0);
      expect($img[0].naturalHeight).to.be.greaterThan(0);
    });
});
