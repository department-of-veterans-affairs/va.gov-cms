import { Given } from "cypress-cucumber-preprocessor/steps";
import faker from "faker";

const creators = {
  documentation_page: () => {
    cy.visit('/node/add/documentation_page');
    cy.scrollTo('top');
    cy.findAllByLabelText('Page title').type('[Test Data] ' + faker.lorem.sentence(), { force: true });
    cy.findAllByLabelText('Page introduction').type(faker.lorem.sentence(), { force: true });
    cy.findAllByLabelText('Section').select('Veterans Affairs', { force: true });
    cy.findAllByLabelText('Parent item').select('-- CMS help center', { force: true });
    return cy.get('form.node-form').find('input#edit-submit').click();
  },
  office: () => {
    cy.visit('/node/add/office');
    cy.scrollTo('top');
    cy.findAllByLabelText('Name').type('[Test Data] ' + faker.lorem.sentence(), { force: true });
    cy.findAllByLabelText('Meta title tag').type('[Test Data] ' + faker.lorem.sentence(), { force: true });
    cy.findAllByLabelText('Section').select('Veterans Affairs', { force: true });
    cy.findAllByLabelText('Provide a menu link').check({ force: true });
    cy.findAllByLabelText('Menu link title').type('[Test Data] ' + faker.lorem.sentence(), { force: true });
    cy.findAllByLabelText('Parent item').select('-- Outreach and events', { force: true });
    return cy.get('form.node-form').find('input#edit-submit').click();
  },
  step_by_step: () => {
    cy.visit('/node/add/step_by_step');
    cy.scrollTo('top');
    cy.findAllByLabelText('Page title').type('[Test Data] ' + faker.lorem.word(), { force: true });

    // Enter text into page intro ckeditor.
    cy.type_ckeditor("edit-field-intro-text-limited-html-0-value", faker.lorem.sentence());

    cy.findAllByLabelText('Section').select('Veterans Affairs', { force: true });
    cy.findAllByLabelText('Button Link').type('https://va.gov/', { force: true });
    cy.findAllByLabelText('Button Label').type('va.gov', { force: true });
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
