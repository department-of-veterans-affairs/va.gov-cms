import { When, Then } from "@badeball/cypress-cucumber-preprocessor";
import { faker } from "@faker-js/faker";

const navigateToAndFillMediaForm = () => {
  cy.visit("/media/add/image");
  cy.injectAxe();
  cy.scrollTo("top");
  cy.findAllByLabelText("Name").type(`[Test Data] ${faker.lorem.sentence()}`, {
    force: true,
  });
  cy.findAllByLabelText("Description").type(faker.lorem.sentence(), {
    force: true,
  });
  cy.findAllByLabelText("Section").select("VACO");
  cy.get("#edit-image-0-upload")
    .attachFile("images/polygon_image.png")
    .wait(1000);
};

const focusOnNameField = () => {
  cy.findAllByLabelText("Name").focus();
};

When("I create an image with {string} as alt-text", (altTextContent) => {
  navigateToAndFillMediaForm();
  cy.findAllByLabelText("Alternative text").type(altTextContent, {
    force: true,
  });
  focusOnNameField();
});

When(
  "I create an image with {int} characters of alt-text content",
  (charCount) => {
    navigateToAndFillMediaForm();
    cy.findAllByLabelText("Alternative text").type(
      faker.helpers.repeatString("a", charCount),
      {
        force: true,
      }
    );
    focusOnNameField();
  }
);

When("I update alt-text content to display {string}", (altTextContent) => {
  cy.findAllByLabelText("Alternative text").clear();
  cy.findAllByLabelText("Alternative text").type(altTextContent, {
    force: true,
  });
});

Then("I should see no error message", () => {
  cy.get("div.form-item--error-message > strong").should(
    "have.attr",
    "style",
    "display: none;"
  );
});
