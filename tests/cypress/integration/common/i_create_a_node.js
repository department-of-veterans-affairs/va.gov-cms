/* eslint-disable max-nested-callbacks */
import { Given } from "cypress-cucumber-preprocessor/steps";
import { faker } from "@faker-js/faker";

const creators = {
  banner: () => {
    cy.findAllByLabelText("Alert type").select("Information", { force: true });
    cy.findAllByLabelText("Heading").type(faker.lorem.sentence(), {
      force: true,
    });
    cy.type_ckeditor("edit-body-0-value", faker.lorem.sentence());
    cy.findAllByLabelText("Section").select("VACO", { force: true });
    return cy.wait(1000);
  },
  basic_landing_page: () => {
    cy.findAllByLabelText("Page title").type(faker.lorem.sentence(), {
      force: true,
    });
    cy.type_ckeditor(
      "edit-field-intro-text-limited-html-0-value",
      faker.lorem.sentence()
    );
    cy.findAllByLabelText("Product").select("All products", { force: true });
    cy.findAllByLabelText("Section").select("VACO", { force: true });
    cy.findAllByLabelText("Meta description").type(faker.lorem.sentence(), {
      force: true,
    });
    cy.addMainContentBlockWithRichText(faker.lorem.sentence());
    return cy.wait(1000);
  },
  campaign_landing_page: () => {
    // Basic page fields
    cy.findAllByLabelText("Page title").type(
      faker.lorem.sentence().substring(0, 50),
      {
        force: true,
      }
    );
    cy.findAllByLabelText("Section").select("VACO", { force: true });
    cy.findAllByLabelText("Page introduction").type(faker.lorem.sentence(), {
      force: true,
    });
    cy.findAllByLabelText("Link").type(faker.internet.url(), {
      force: true,
    });
    cy.findAllByLabelText("Link text").type(
      faker.lorem.sentence().substring(0, 35),
      {
        force: true,
      }
    );

    // Hero banner
    cy.contains("Hero banner").scrollIntoView().click({ force: true });
    cy.contains("Hero banner")
      .parent()
      .then(($el) => {
        cy.wrap($el).contains("Add media").click({ force: true });
        cy.get(".dropzone", {
          timeout: 10000,
        });
        cy.get(".dropzone").attachFile("images/polygon_image.png", {
          subjectType: "drag-n-drop",
        });
        cy.wait(1000);
        cy.findAllByLabelText("Alternative text").type(faker.lorem.sentence(), {
          force: true,
        });
        cy.get('div[role="dialog"]').within(() => {
          cy.findAllByLabelText("Section").select("VACO", { force: true });
        });
        cy.get("button").contains("Save and insert").click({ force: true });
      });
    cy.contains("Hero banner").click({ force: true });

    // Why this matters
    cy.contains("Why this matters").scrollIntoView().click({ force: true });
    cy.contains("Why this matters")
      .parent()
      .findAllByLabelText("Introduction")
      .type(faker.lorem.sentence(), {
        force: true,
      });
    cy.contains("Why this matters").click();

    // What you can do
    cy.contains("What you can do").scrollIntoView().click({ force: true });
    cy.contains("What you can do")
      .parent()
      .within(() => {
        cy.findAllByLabelText("Heading").type(faker.lorem.sentence(), {
          force: true,
        });
        cy.findAllByLabelText("Introduction").type(faker.lorem.sentence(), {
          force: true,
        });
        cy.get("#edit-field-clp-what-you-can-do-promos-actions-ief-add")
          .scrollIntoView()
          .click({ force: true });
        cy.contains("Add media").click({ force: true });
      });
    cy.get('div[role="dialog"]').within(() => {
      cy.get(".dropzone", {
        timeout: 10000,
      });
      cy.get(".dropzone").attachFile("images/polygon_image.png", {
        subjectType: "drag-n-drop",
      });
      cy.wait(1000);
      cy.findAllByLabelText("Alternative text").type(faker.lorem.sentence(), {
        force: true,
      });
      cy.get(
        '[data-drupal-selector="edit-media-0-fields-field-owner"]'
      ).select("VACO", { force: true });
      cy.get("button").contains("Save and insert").click({ force: true });
    });
    cy.contains("What you can do")
      .parent()
      .within(() => {
        cy.findAllByLabelText("URL")
          .focus()
          .type(faker.internet.url(), { force: true });
        cy.findAllByLabelText("Link text").type(faker.lorem.sentence(), {
          force: true,
        });
        cy.findAllByLabelText("Section").select("VACO", { force: true });
      });
    cy.contains("What you can do").click();

    // VA Benefits
    cy.contains("VA Benefits").scrollIntoView().click({ force: true });
    cy.contains("VA Benefits")
      .parent()
      .within(() => {
        cy.contains("Related benefits").scrollIntoView().click({ force: true });
        cy.contains("Select Benefit Hub(s)").click({ force: true });
      });
    cy.get("iframe.entity-browser-modal-iframe").should("exist");
    cy.wait(3000);
    cy.get("iframe.entity-browser-modal-iframe")
      .iframe()
      .within(() => {
        cy.get("tr")
          .contains("VA Careers and employment")
          .should("exist")
          .parent()
          .find("[type='checkbox']")
          .check({ force: true });
        cy.get("#edit-submit").click({ force: true });
      });
    cy.get("iframe.entity-browser-modal-iframe").should("not.exist");
    cy.contains("VA Benefits").click();

    return cy.wait(1000);
  },
  checklist: () => {
    cy.findAllByLabelText(
      "Page title"
    ).type(`[Test Data] ${faker.lorem.sentence(3)}`, { force: true });
    cy.findAllByLabelText("Page introduction").type(faker.lorem.sentence(), {
      force: true,
    });
    cy.findAllByLabelText("Section").select("VACO", { force: true });
    cy.findAllByLabelText("Primary category").select("Burials and memorials", {
      force: true,
    });
    cy.get(
      "#edit-field-related-information-0-subform-field-link-0-uri"
    ).type("http://www.example.com/", { force: true });
    cy.get(
      "#edit-field-related-information-0-subform-field-link-0-title"
    ).type(`[Test Link Title]${faker.lorem.sentence()}`, { force: true });
    cy.get(
      "#edit-field-checklist-0-subform-field-checklist-sections-0-subform-field-section-header-0-value"
    ).type(`[Test Header Value]${faker.lorem.sentence(3)}`, { force: true });
    cy.get(
      "#edit-field-checklist-0-subform-field-checklist-sections-0-subform-field-checklist-items-0-value"
    ).type(`[Test Items Value]${faker.lorem.sentence()}`, { force: true });
    cy.contains("All Veterans").parent().find("input").check({ force: true });
    return cy.wait(1000);
  },
  documentation_page: () => {
    cy.findAllByLabelText(
      "Page title"
    ).type(`[Test Data] ${faker.lorem.sentence()}`, { force: true });
    cy.findAllByLabelText("Page introduction").type(faker.lorem.sentence(), {
      force: true,
    });
    cy.findAllByLabelText("Section").select("VACO", { force: true });
    cy.findAllByLabelText(
      "Parent link"
    ).select("-- CMS Knowledge Base (disabled)", { force: true });
    cy.findAllByLabelText("All products").check({ force: true });
    return cy.wait(1000);
  },
  event: () => {
    cy.findAllByLabelText("Name").type(
      `[Test Data] ${faker.lorem.sentence()}`,
      { force: true }
    );
    cy.get(
      "#edit-field-datetime-range-timezone-0-time-wrapper-value-date"
    ).type("2023-04-05", { force: true });
    cy.get(
      "#edit-field-datetime-range-timezone-0-time-wrapper-value-time"
    ).type("12:00", { force: true });
    cy.findAllByLabelText(
      "Where should the event be listed?"
    ).select("VA Alaska health care: Events", { force: true });
    cy.findAllByLabelText("Street address").type(
      faker.address.streetAddress(),
      { force: true }
    );
    cy.findAllByLabelText("City").type(faker.address.city(), { force: true });
    cy.findAllByLabelText("State").select("Alabama", { force: true });
    cy.findAllByLabelText("Section").select("--Outreach Hub", { force: true });
    cy.scrollToSelector("#edit-field-media-open-button");
    cy.get("#edit-field-media-open-button").click({ force: true });
    cy.get(".dropzone", {
      timeout: 10000,
    });
    cy.get(".dropzone").attachFile("images/polygon_image.png", {
      subjectType: "drag-n-drop",
    });
    cy.wait(1000);
    cy.findAllByLabelText("Alternative text").type(faker.lorem.sentence(), {
      force: true,
    });
    cy.get(
      '[data-drupal-selector="edit-media-0-fields-field-owner"]'
    ).select("VACO", { force: true });
    cy.get("button").contains("Save and insert").click({ force: true });
    cy.get(
      'div.media-library-item[data-drupal-selector="edit-field-media-selection-0"]',
      {
        timeout: 15000,
      }
    );
    return cy.wait(1000);
  },
  health_care_region_detail_page: () => {
    cy.findAllByLabelText(
      "Page title"
    ).type(`[Test Data] ${faker.lorem.sentence()}`, { force: true });
    cy.findAllByLabelText("Page introduction").type(faker.lorem.sentence(), {
      force: true,
    });
    cy.findAllByLabelText("Section").select("VACO", { force: true });
    cy.findAllByLabelText(
      "Related office or health care system"
    ).select("VA Alaska health care", { force: true });
    cy.findAllByLabelText(
      "Parent link"
    ).select("-------- Anchorage VA Medical Center", { force: true });
    cy.findAllByLabelText("Meta description").type(faker.lorem.sentence(), {
      force: true,
    });
    return cy.wait(1000);
  },
  landing_page: () => {
    cy.findAllByLabelText(
      "Page title"
    ).type(`[Test Data] ${faker.lorem.sentence()}`, { force: true });
    cy.findAllByLabelText("Page introduction").type(faker.lorem.sentence(), {
      force: true,
    });
    cy.findAllByLabelText("Section").select("VACO", { force: true });
    cy.findAllByLabelText("Meta description").type(faker.lorem.sentence(), {
      force: true,
    });
    cy.findAllByLabelText("Provide a menu link").check({ force: true });
    cy.findAllByLabelText(
      "Menu link title"
    ).type(`[Test Data] ${faker.lorem.sentence()}`, { force: true });
    cy.findAllByLabelText("Parent link").select("---- Disability", {
      force: true,
    });
    cy.contains("Add List of link teasers").click({ force: true });
    cy.get(
      "input[id^=edit-field-spokes-0-subform-field-va-paragraphs-0-subform-field-link-0-uri"
    ).type(faker.internet.url(), { force: true });
    cy.get(
      "input[id^=edit-field-spokes-0-subform-field-va-paragraphs-0-subform-field-link-0-title"
    ).type(faker.company.companyName(), { force: true });
    return cy.wait(3000);
  },
  office: () => {
    cy.findAllByLabelText("Name").type(
      `[Test Data] ${faker.lorem.sentence()}`,
      { force: true }
    );
    cy.findAllByLabelText("Section").select("---VA Sheridan health care", {
      force: true,
    });
    cy.findAllByLabelText("Provide a menu link").check({ force: true });
    cy.findAllByLabelText(
      "Menu link title"
    ).type(`[Test Data] ${faker.lorem.sentence()}`, { force: true });
    cy.findAllByLabelText("Parent link").select("-- Outreach and events", {
      force: true,
    });
    return cy.wait(1000);
  },
  step_by_step: () => {
    cy.findAllByLabelText(
      "Page title"
    ).type(`[Test Data] ${faker.lorem.word()}`, { force: true });
    cy.findAllByLabelText("Page introduction").type(faker.lorem.sentence(), {
      force: true,
    });
    cy.type_ckeditor(
      "edit-field-intro-text-limited-html-0-value",
      faker.lorem.sentence()
    );
    cy.findAllByLabelText("Section").select("VACO", { force: true });
    cy.findAllByLabelText("Section Header").type(faker.lorem.word(), {
      force: true,
    });
    cy.findAllByLabelText("Text").type(faker.lorem.sentence(), { force: true });
    cy.findAllByLabelText("URL").type("https://va.gov/", { force: true });
    cy.findAllByLabelText("Link text").type("va.gov", { force: true });
    cy.findAllByLabelText("Primary category").select("Records", {
      force: true,
    });
    cy.findAllByLabelText("Claims and appeals status").check({ force: true });
    cy.type_ckeditor(
      "edit-field-steps-0-subform-field-step-0-subform-field-wysiwyg-0-value",
      faker.lorem.sentence()
    );
    return cy.wait(1000);
  },
};

Given("I create a {string} node", (contentType) => {
  const creator = creators[contentType];
  assert.isNotNull(
    creator,
    `I do not know how to create ${contentType} nodes yet.  Please add a definition in ${__filename}.`
  );
  cy.visit(`/node/add/${contentType}`);
  cy.injectAxe();
  cy.scrollTo("top");
  cy.checkAccessibility();
  creator().then(() => {
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

Given("I create a {string} node and continue", (contentType) => {
  const creator = creators[contentType];
  assert.isDefined(
    creator,
    `I do not know how to create ${contentType} nodes yet.  Please add a definition in ${__filename}.`
  );
  cy.visit(`/node/add/${contentType}`);
  cy.injectAxe();
  cy.scrollTo("top");
  cy.checkAccessibility();
  creator().then(() => {
    cy.get("form.node-form").find("input#edit-save-continue").click();
    cy.location("pathname", { timeout: 10000 }).should(
      "not.include",
      "/node/add"
    );
    cy.injectAxe();
    cy.checkAccessibility();
    cy.drupalWatchdogHasNoNewErrors();
    cy.getDrupalSettings().then((drupalSettings) => {
      const { currentPath } = drupalSettings.path;
      const pathComponents = currentPath.split("/");
      pathComponents.pop();
      cy.wrap(pathComponents.pop()).as("nodeId");
      cy.window().then((window) => {
        const pagePath = window.location.pathname;
        cy.wrap(pagePath).as("pagePath");
      });
    });
  });
});
