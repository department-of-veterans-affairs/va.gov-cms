import { Given } from "cypress-cucumber-preprocessor/steps";
import faker from "faker";

const creators = {
  documentation_page: () => {
    cy.visit('/node/add/documentation_page');
    cy.scrollTo('top');
    cy.findAllByLabelText('Page title').type('[Test Data] ' + faker.lorem.sentence(), { force: true });
    cy.findAllByLabelText('Page introduction').type(faker.lorem.sentence(), { force: true });
    cy.findAllByLabelText('Section').select('VACO', { force: true });
    cy.findAllByLabelText('Parent link').select('-- CMS Knowledge Base (disabled)', { force: true });
    return cy.get('form.node-form').find('input#edit-submit').click();
  },
  event: () => {
    cy.visit('/node/add/event');
    cy.scrollTo('top');
    cy.findAllByLabelText('Name').type('[Test Data] ' + faker.lorem.sentence(), { force: true });
    cy.get('#edit-field-datetime-range-timezone-0-value-date').type('2023-12-05', { force: true });
    cy.get('#edit-field-datetime-range-timezone-0-value-time').type('12:00', { force: true });
    cy.findAllByLabelText('Where should the event be listed?').select('VA Alaska health care: Events', { force: true });
    cy.findAllByLabelText('Street address').type(faker.address.streetAddress(), { force: true });
    cy.findAllByLabelText('City').type(faker.address.city(), { force: true });
    cy.findAllByLabelText('State').select('Alabama', { force: true });
    cy.findAllByLabelText('Section').select('VACO', { force: true });
    cy.scrollToSelector('#edit-field-media-open-button');
    cy.get('#edit-field-media-open-button').click({ force: true });
    cy.get('.dropzone', {
      timeout: 10000,
    });
    cy.get('.dropzone').attachFile('images/polygon_image.png', {
      subjectType: 'drag-n-drop',
    });
    cy.wait(1000);
    cy.findAllByLabelText('Alternative text').type(faker.lorem.sentence(), { force: true });
    cy.get('[data-drupal-selector="edit-media-0-fields-field-owner"]').select('VACO', { force: true });
    cy.get('button').contains('Save and insert').click({ force: true });
    cy.get('div.media-library-item[data-drupal-selector="edit-field-media-selection-0"]', {
      timeout: 15000,
    });
    return cy.get('form.node-form').find('input#edit-submit').click();
  },
  health_care_region_detail_page: () => {
    cy.visit('/node/add/health_care_region_detail_page');
    cy.scrollTo('top');
    cy.findAllByLabelText('Page title').type('[Test Data] ' + faker.lorem.sentence(), { force: true });
    cy.findAllByLabelText('Page introduction').type(faker.lorem.sentence(), { force: true });
    cy.findAllByLabelText('Section').select('VACO', { force: true });
    cy.findAllByLabelText('Related office or health care system').select('VA Alaska health care', { force: true });
    cy.findAllByLabelText('Parent link').select('-------- Anchorage VA Medical Center', { force: true });
    cy.findAllByLabelText('Meta description').type(faker.lorem.sentence(), { force: true });
    return cy.get('form.node-form').find('input#edit-submit').click();
  },
  office: () => {
    cy.visit('/node/add/office');
    cy.scrollTo('top');
    cy.findAllByLabelText('Name').type('[Test Data] ' + faker.lorem.sentence(), { force: true });
    cy.findAllByLabelText('Meta title tag').type('[Test Data] ' + faker.lorem.sentence(), { force: true });
    cy.findAllByLabelText('Section').select('VACO', { force: true });
    cy.findAllByLabelText('Provide a menu link').check({ force: true });
    cy.findAllByLabelText('Menu link title').type('[Test Data] ' + faker.lorem.sentence(), { force: true });
    cy.findAllByLabelText('Parent link').select('-- Outreach and events', { force: true });
    return cy.get('form.node-form').find('input#edit-submit').click();
  },
  step_by_step: () => {
    cy.visit('/node/add/step_by_step');
    cy.scrollTo('top');
    cy.findAllByLabelText('Page title').type('[Test Data] ' + faker.lorem.word(), { force: true });
    cy.findAllByLabelText('Page introduction').type(faker.lorem.sentence(), { force: true });

    // Enter text into page intro ckeditor.
    cy.type_ckeditor("edit-field-intro-text-limited-html-0-value", faker.lorem.sentence());
    cy.findAllByLabelText('Section').select('VACO', { force: true });
    cy.findAllByLabelText('Section Header').type(faker.lorem.word(), { force: true });
    cy.findAllByLabelText('Text').type(faker.lorem.sentence(), { force: true });
    cy.findAllByLabelText('URL').type('https://va.gov/', { force: true });
    cy.findAllByLabelText('Link text').type('va.gov', { force: true });
    cy.findAllByLabelText('Primary category').select('Records', { force: true });
    cy.findAllByLabelText('Claims and appeals status').check({ force: true });
    cy.type_ckeditor("edit-field-steps-0-subform-field-step-0-subform-field-wysiwyg-0-value", faker.lorem.sentence());
    return cy.get('form.node-form').find('input#edit-submit').click();
  },
};

Given('I create a {string} node', (contentType) => {
  let creator = creators[contentType];
  assert.isNotNull(creator, `I do not know how to create ${contentType} nodes yet.  Please add a definition in ${__filename}.`);
  creator().then(() => {
    cy.getDrupalSettings().then((drupalSettings) => {
      const currentPath = drupalSettings.path.currentPath;
      cy.wrap(currentPath.split('/').pop()).as('nodeId');
      cy.window().then((window) => {
        const pagePath = window.location.pathname;
        cy.wrap(pagePath).as('pagePath');
      });
    });
  });
});
