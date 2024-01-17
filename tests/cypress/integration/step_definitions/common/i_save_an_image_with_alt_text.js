import { When } from "@badeball/cypress-cucumber-preprocessor";
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

const clickSaveButton = () => {
  cy.get("form.media-form input#edit-submit").click();
  cy.wait(1000);
};

When("I save an image with {string} as alt-text", (altTextContent) => {
  navigateToAndFillMediaForm();
  cy.findAllByLabelText("Alternative text").type(altTextContent, {
    force: true,
  });
  clickSaveButton();
});

When(
  "I save an image with {int} characters of alt-text content",
  (charCount) => {
    navigateToAndFillMediaForm();
    cy.findAllByLabelText("Alternative text").type(
      faker.helpers.repeatString("a", charCount),
      {
        force: true,
      }
    );
    clickSaveButton();
  }
);
