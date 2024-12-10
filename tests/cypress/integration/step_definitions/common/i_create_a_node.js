/* eslint-disable max-nested-callbacks */
import { Given } from "@badeball/cypress-cucumber-preprocessor";
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
    cy.findAllByLabelText("Page title").type(
      faker.lorem.sentence().substring(0, 50),
      {
        force: true,
      }
    );
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
    cy.contains("Hero banner").scrollIntoView();
    cy.contains("Hero banner").click({ force: true });
    cy.contains("Hero banner")
      .parent()
      .then(($el) => {
        cy.wrap($el).contains("Add media").click({ force: true });
        cy.get(".dropzone", {
          timeout: 60000,
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
    cy.contains("Why this matters").scrollIntoView();
    cy.contains("Why this matters").click({ force: true });
    cy.contains("Why this matters")
      .parent()
      .findAllByLabelText("Introduction")
      .type(faker.lorem.sentence(), {
        force: true,
      });
    cy.contains("Why this matters").click();

    // What you can do
    cy.contains("What you can do").scrollIntoView();
    cy.contains("What you can do").click({ force: true });
    cy.contains("What you can do")
      .parent()
      .within(() => {
        cy.findAllByLabelText("Heading").type(
          faker.lorem.sentence().substring(0, 50),
          {
            force: true,
          }
        );
        cy.findAllByLabelText("Introduction").type(faker.lorem.sentence(), {
          force: true,
        });
      });
    cy.get(
      "#edit-field-clp-what-you-can-do-promos-entity-browser-entity-browser-open-modal"
    ).should("exist");
    cy.get(
      "#edit-field-clp-what-you-can-do-promos-entity-browser-entity-browser-open-modal"
    ).click({
      force: true,
    });
    cy.wait(3000);
    cy.get("iframe.entity-browser-modal-iframe").should("exist");
    cy.wait(3000);

    cy.get("iframe.entity-browser-modal-iframe")
      .iframe()
      .within(() => {
        cy.contains("Add new promo").click({ force: true });
        cy.wait(5000);
      });
    cy.get("iframe.entity-browser-modal-iframe").should("exist");
    cy.get("iframe.entity-browser-modal-iframe")
      .iframe()
      .within(() => {
        cy.findByDisplayValue("Add media").click({ force: true });
      });
    cy.get("iframe.entity-browser-modal-iframe")
      .iframe()
      .within(() => {
        cy.get('div[role="dialog"]').within(() => {
          cy.get(".dropzone", {
            timeout: 60000,
          });
        });
      });
    cy.get("iframe.entity-browser-modal-iframe")
      .iframe()
      .within(() => {
        cy.get(".dropzone").attachFile("images/polygon_image.png", {
          subjectType: "drag-n-drop",
        });
        cy.wait(10000);
      });
    cy.get("iframe.entity-browser-modal-iframe")
      .iframe()
      .within(() => {
        cy.findAllByLabelText("Alternative text").type(faker.lorem.sentence(), {
          force: true,
        });
      });
    cy.get("iframe.entity-browser-modal-iframe")
      .iframe()
      .within(() => {
        cy.get(
          '[data-drupal-selector="edit-media-0-fields-field-owner"]'
        ).select("VACO", { force: true });
      });
    cy.get("iframe.entity-browser-modal-iframe")
      .iframe()
      .within(() => {
        cy.get("button").contains("Save and insert").click({ force: true });
        cy.wait(5000);
      });
    cy.get("iframe.entity-browser-modal-iframe")
      .iframe()
      .within(() => {
        cy.findAllByLabelText("URL").type(faker.internet.url(), {
          force: true,
        });
      });
    cy.get("iframe.entity-browser-modal-iframe")
      .iframe()
      .within(() => {
        cy.findAllByLabelText("Link text").type(faker.lorem.sentence(), {
          force: true,
        });
      });
    cy.get("iframe.entity-browser-modal-iframe")
      .iframe()
      .within(() => {
        cy.get(
          '[data-drupal-selector="edit-inline-entity-form-field-owner"]'
        ).select("VACO", { force: true });
      });
    cy.get("iframe.entity-browser-modal-iframe")
      .iframe()
      .within(() => {
        cy.get("#edit-submit").click({ force: true });
      });
    cy.get("iframe.entity-browser-modal-iframe").should("not.exist");

    cy.contains("What you can do").click();

    // VA Benefits
    cy.get("#edit-group-va-benefits").scrollIntoView();
    cy.get("#edit-group-va-benefits").click({ force: true });
    cy.get("#edit-group-va-benefits")
      .parent()
      .within(() => {
        cy.contains("Related benefits").scrollIntoView();
        cy.contains("Related benefits").click({ force: true });
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
    cy.get("#edit-group-va-benefits").click();

    return cy.wait(1000);
  },
  checklist: () => {
    cy.findAllByLabelText("Page title").type(
      `[Test Data] ${faker.lorem.sentence(3)}`,
      { force: true }
    );
    cy.findAllByLabelText("Page introduction").type(faker.lorem.sentence(), {
      force: true,
    });
    cy.findAllByLabelText("Section").select("VACO", { force: true });
    cy.findAllByLabelText("Primary category").select("Burials and memorials", {
      force: true,
    });
    cy.get("#edit-field-related-information-0-subform-field-link-0-uri").type(
      "http://www.example.com/",
      { force: true }
    );
    cy.get("#edit-field-related-information-0-subform-field-link-0-title").type(
      `[Test Link Title]${faker.lorem.sentence()}`,
      { force: true }
    );
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
    cy.findAllByLabelText("Page title").type(
      `[Test Data] ${faker.lorem.sentence()}`,
      { force: true }
    );
    cy.findAllByLabelText("Page introduction").type(faker.lorem.sentence(), {
      force: true,
    });
    cy.findAllByLabelText("Section").select("VACO", { force: true });
    cy.findAllByLabelText("Parent link").select(
      "-- CMS Knowledge Base (disabled)",
      { force: true }
    );
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
    ).type("2023-11-04", { force: true });
    cy.get(
      "#edit-field-datetime-range-timezone-0-time-wrapper-value-date"
    ).type("2023-11-04", { force: true });
    cy.get(
      "#edit-field-datetime-range-timezone-0-time-wrapper-value-time"
    ).type("10:00:00", { force: true });
    cy.get(
      "#edit-field-datetime-range-timezone-0-time-wrapper-end-value-time"
    ).type("11:00:00", { force: true });
    cy.get("#edit-field-datetime-range-timezone-0-timezone").select("Phoenix");
    cy.get("#edit-field-datetime-range-timezone-0-make-recurring").check();
    cy.get("#edit-field-datetime-range-timezone-0-interval").type("1");
    cy.get("#edit-field-datetime-range-timezone-0-repeat-end-date").type(
      "2023-11-07",
      { force: true }
    );
    cy.get("#edit-field-datetime-range-timezone-0-repeat").select("DAILY");
    cy.findAllByLabelText("Where should the event be listed?").select(
      "VA Alaska health care: Events",
      { force: true }
    );
    cy.get("#edit-field-publish-to-outreach-cal-value").check();
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
      timeout: 60000,
    }).should("exist");
    cy.get(".dropzone").attachFile("images/polygon_image.png", {
      subjectType: "drag-n-drop",
    });
    cy.findAllByLabelText("Alternative text").type(faker.lorem.sentence(), {
      force: true,
    });
    cy.get('[data-drupal-selector="edit-media-0-fields-field-owner"]').select(
      "VACO",
      { force: true }
    );
    cy.get("#edit-revision-log-0-value").type(
      `[Test revision log 1]${faker.lorem.sentence()}`,
      { force: true }
    );
    cy.get("button").contains("Save and insert").click({ force: true });
    cy.get(
      'div.media-library-item[data-drupal-selector="edit-field-media-selection-0"]',
      {
        timeout: 20000,
      }
    ).should("exist");
    cy.get("#edit-revision-log-0-value").type(
      `[Test revision log]${faker.lorem.sentence()}`,
      { force: true }
    );
    cy.get("form.node-form").find("input#edit-submit").click();
    cy.get(".node__content").contains("Sun, Nov 5 2023, 10:00am - 11:00am MST");
    cy.get(".node__content").contains("Outreach events");
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
  health_care_region_detail_page: () => {
    cy.findAllByLabelText("Page title").type(
      `[Test Data] ${faker.lorem.sentence()}`,
      { force: true }
    );
    cy.findAllByLabelText("Page introduction").type(faker.lorem.sentence(), {
      force: true,
    });
    cy.findAllByLabelText("Section").select("VACO", { force: true });
    cy.findAllByLabelText("Related office or health care system").select(
      "VA Alaska health care",
      { force: true }
    );
    cy.findAllByLabelText("Parent link").select(
      "-------- Colonel Mary Louise Rasmuson",
      { force: true }
    );
    cy.findAllByLabelText("Meta description").type(faker.lorem.sentence(), {
      force: true,
    });
    return cy.wait(1000);
  },
  landing_page: () => {
    cy.findAllByLabelText("Page title").type(
      `[Test Data] ${faker.lorem.sentence()}`,
      { force: true }
    );
    cy.findAllByLabelText("Page introduction").type(faker.lorem.sentence(), {
      force: true,
    });
    cy.findAllByLabelText("Section").select("VACO", { force: true });
    cy.findAllByLabelText("Meta description").type(faker.lorem.sentence(), {
      force: true,
    });
    cy.findAllByLabelText("Provide a menu link").check({ force: true });
    cy.findAllByLabelText("Menu link title").type(
      `[Test Data] ${faker.lorem.sentence()}`,
      { force: true }
    );
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
    cy.findAllByLabelText("Menu link title").type(
      `[Test Data] ${faker.lorem.sentence()}`,
      { force: true }
    );
    cy.findAllByLabelText("Parent link").select("-- Outreach and events", {
      force: true,
    });
    return cy.wait(1000);
  },
  step_by_step: () => {
    cy.findAllByLabelText("Page title").type(
      `[Test Data] ${faker.lorem.word()}`,
      { force: true }
    );
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
  q_a: () => {
    cy.findAllByLabelText("Question").type(
      `[Test Data] ${faker.lorem.word()}`,
      { force: true }
    );
    cy.findAllByLabelText("Text").type(faker.lorem.sentence(), {
      force: true,
    });
    cy.type_ckeditor(
      "edit-field-answer-0-subform-field-wysiwyg-0-value",
      faker.lorem.sentence()
    );
    cy.findAllByLabelText("Section").select("VACO", { force: true });
    return cy;
  },
  press_release: () => {
    cy.findAllByLabelText("Section").select("VACO", { force: true });
    cy.findAllByLabelText("Press Release Title").type(
      `[Test Data] ${faker.lorem.word()}`,
      { force: true }
    );
    cy.findAllByLabelText("News releases listing").select(
      "VA Albany health care: News releases",
      { force: true }
    );
    cy.findAllByLabelText("City").type(`Albany`, { force: true });
    cy.findAllByLabelText("State").select("New York", { force: true });
    cy.findAllByLabelText("Introduction").type(faker.lorem.sentence(), {
      force: true,
    });
    cy.type_ckeditor(
      "edit-field-press-release-fulltext-0-value",
      faker.lorem.paragraph()
    );
    return cy;
  },
  news_story: () => {
    cy.findAllByLabelText("Section").select("VACO", { force: true });
    cy.findAllByLabelText("Title").type(`[Test Data] ${faker.lorem.word()}`, {
      force: true,
    });
    cy.findAllByLabelText("Where should the story be listed?").select(
      "VA Albany health care: Stories",
      { force: true }
    );
    cy.findAllByLabelText("First sentence (lede)").type(
      faker.lorem.sentence(),
      {
        force: true,
      }
    );
    return cy;
  },
  page: () => {
    cy.findAllByLabelText("Section").select("VACO", { force: true });
    cy.findAllByLabelText("Page title").type(
      `[Test Data] ${faker.lorem.word()}`,
      { force: true }
    );
    cy.type_ckeditor(
      "edit-field-intro-text-limited-html-0-value",
      faker.lorem.paragraph()
    );
    cy.findAllByLabelText("Meta description").type(faker.lorem.sentence(), {
      force: true,
    });
    cy.scrollToSelector("#edit-field-content-block-add-more-browse");
    cy.get("#edit-field-content-block-add-more-browse").click({ force: true });
    cy.findByText("An open-ended text field.").click({ force: true });
    cy.type_ckeditor(
      "edit-field-content-block-0-subform-field-wysiwyg-0-value",
      faker.lorem.paragraph()
    );
    return cy;
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
    cy.get("#edit-revision-log-0-value").type(
      `[Test revision log]${faker.lorem.sentence()}`,
      { force: true }
    );
    cy.get("form.node-form").find("input#edit-save-continue").click();
    cy.location("pathname", { timeout: 10000 }).should(
      "not.include",
      "/node/add"
    );
    cy.injectAxe();
    cy.checkAccessibility();
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
