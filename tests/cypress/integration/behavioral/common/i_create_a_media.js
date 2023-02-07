import { Given } from "cypress-cucumber-preprocessor/steps";
import { faker } from "@faker-js/faker";

const creators = {
  document: () => {
    cy.visit("/media/add/document");
    cy.scrollTo("top");
    cy.findAllByLabelText("Name").type(
      `[Test Data] ${faker.lorem.sentence()}`,
      { force: true }
    );
    cy.findAllByLabelText("Section").select("VACO");
    cy.get("#edit-field-document-0-upload")
      .attachFile("documents/minimal.csv")
      .wait(1000);
    cy.get("form.media-form").find("input#edit-submit").click();
    return cy.wait(1000);
  },
  document_external: () => {
    cy.visit("/media/add/document_external");
    cy.scrollTo("top");
    cy.findAllByLabelText("Name").type(
      `[Test Data] ${faker.lorem.sentence()}`.substring(0, 60),
      {
        force: true,
      }
    );
    cy.findAllByLabelText("External File URL").type(
      "https://en.wikipedia.org/wiki/Stradella_bass_system#/media/File:120-button_Stradella_chart.pdf",
      {
        force: true,
      }
    );
    cy.findAllByLabelText("Description").type(faker.lorem.sentence(), {
      force: true,
    });
    cy.findAllByLabelText("Section").select("VACO");
    cy.get("form.media-form").find("input#edit-submit").click();
    return cy.wait(1000);
  },
  image: () => {
    cy.visit("/media/add/image");
    cy.scrollTo("top");
    cy.findAllByLabelText("Name").type(
      `[Test Data] ${faker.lorem.sentence()}`,
      { force: true }
    );
    cy.findAllByLabelText("Description").type(faker.lorem.sentence(), {
      force: true,
    });
    cy.findAllByLabelText("Section").select("VACO");
    cy.get("#edit-image-0-upload")
      .attachFile("images/polygon_image.png")
      .wait(1000);
    cy.findAllByLabelText("Alternative text").type(faker.lorem.sentence(), {
      force: true,
    });
    cy.window().then((window) => {
      window.jQuery('details[data-drupal-iwc-id="2_1"] > summary').click();
      cy.wait(1000);
      cy.get("span.cropper-face.cropper-move").scrollIntoView();
    });
    cy.window().then((window) => {
      const POINTER_DOWN = window.PointerEvent ? "pointerdown" : "mousedown";
      const POINTER_MOVE = window.PointerEvent ? "pointermove" : "mousemove";
      const POINTER_UP = window.PointerEvent ? "pointerup" : "mouseup";
      const cropperType = window
        .jQuery("[data-drupal-iwc=wrapper]")
        .data("ImageWidgetCrop").types[0];
      const { cropper } = cropperType;
      const { dragBox } = cropper;
      const $wrapper = window.jQuery(dragBox).closest(".crop-preview-wrapper");
      const $cropBox = $wrapper.find(".cropper-crop-box");
      const $points = $wrapper.find(".cropper-point");
      const moveBox = window
        .jQuery(".cropper-face.cropper-move")[0]
        .getBoundingClientRect();
      cy.wrap(dragBox)
        .trigger("mouseover", { force: true })
        .wait(100)
        .trigger(POINTER_DOWN, { which: 1, force: true })
        .wait(100)
        .trigger(POINTER_MOVE, 15, 15, { which: 1, force: true })
        .wait(100)
        .trigger(POINTER_MOVE, 50, 50, { which: 1, force: true })
        .wait(100)
        .trigger(POINTER_UP, { which: 1, force: true });
    });
    cy.window().then((window) => {
      const mediaImageUrl = window
        .jQuery("span.file--image")
        .find("a")
        .attr("href");
      cy.wrap(mediaImageUrl).as("mediaImageUrl");
    });
    cy.get("form.media-form").find("input#edit-submit").click();
    return cy.wait(1000);
  },
  video: () => {
    cy.visit("/media/add/video");
    cy.scrollTo("top");
    cy.findAllByLabelText("Name").type(
      `[Test Data] ${faker.lorem.sentence()}`,
      { force: true }
    );
    cy.findAllByLabelText("Video URL").type(
      "https://www.youtube.com/watch?v=XuXax5-pWzI",
      {
        force: true,
      }
    );
    cy.findAllByLabelText("Description").type(faker.lorem.sentence(), {
      force: true,
    });
    cy.findAllByLabelText("Section").select("VACO");
    cy.get("form.media-form").find("input#edit-submit").click();
    return cy.wait(1000);
  },
};

Given("I create a {string} media", (contentType) => {
  const creator = creators[contentType];
  assert.isDefined(
    creator,
    `I do not know how to create ${contentType} media yet.  Please add a definition in ${__filename}.`
  );
  creator().then(() => {
    cy.location("pathname", { timeout: 10000 }).should(
      "not.include",
      "/media/add"
    );
    cy.xpath('//div[@class="messages__content"]/em[@class="placeholder"]/a')
      .first()
      .then(($element) => {
        cy.drupalWatchdogHasNoNewErrors();
        const mediaPath = $element.attr("href");
        const pathComponents = mediaPath.split("/");
        const mediaId = pathComponents.pop();
        cy.wrap(mediaPath).as("mediaPath");
        cy.wrap(mediaId).as("mediaId");
        cy.wrap(mediaPath).as("pagePath");
        return cy.visit(mediaPath);
      });
  });
});
