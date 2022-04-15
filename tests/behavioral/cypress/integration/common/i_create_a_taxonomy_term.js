import { Given } from "cypress-cucumber-preprocessor/steps";
import faker from "faker";

const creators = {
  audience_beneficiaries: () => {
    cy.visit('/admin/structure/taxonomy/manage/audience_beneficiaries/add');
    cy.scrollTo('top');
    return cy.findAllByLabelText('Name').type('[Test Data] ' + faker.lorem.sentence(), { force: true });
  },
  products: () => {
    cy.visit('/admin/structure/taxonomy/manage/products/add');
    cy.scrollTo('top');
    cy.findAllByLabelText('Name').type('[Test Data] ' + faker.lorem.sentence(), { force: true });
    cy.findAllByLabelText('Description').type(faker.lorem.sentence(), { force: true });
    return cy.get('form.taxonomy-term-form').find('input#edit-submit').click();
  },
  health_care_service_taxonomy: () => {
    cy.visit('/admin/structure/taxonomy/manage/health_care_service_taxonomy/add');
    cy.scrollTo('top');
    cy.findAllByLabelText('Name').type('[Test Data] ' + faker.lorem.sentence(), { force: true });
    cy.findAllByLabelText('Description').type(faker.lorem.sentence(), { force: true });
    return cy.findAllByLabelText('Health Service API ID').type(faker.datatype.number(), { force: true });
  },
};

Given('I create a {string} taxonomy term', (vocabulary) => {
  let creator = creators[vocabulary];
  assert.isNotNull(creator, `I do not know how to create ${vocabulary} taxonomy terms yet.  Please add a definition in ${__filename}.`);
  creator().then(() => {
    cy.get('form.taxonomy-term-form').find('input#edit-submit').click();
    cy.getDrupalSettings().then((drupalSettings) => {
      cy.getLastCreatedTaxonomyTerm().then((tidCommand) => {
        cy.log(tidCommand);
        cy.wrap(tidCommand.stdout).as('termId');
      });
      cy.window().then((window) => {
        const pagePath = window.location.pathname;
        cy.wrap(pagePath).as('pagePath');
      });
    });
  });
});
