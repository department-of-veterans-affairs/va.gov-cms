import { Given } from "cypress-cucumber-preprocessor/steps";
import faker from "faker";

const creators = {
  products: () => {
    cy.visit('/admin/structure/taxonomy/manage/products/add');
    cy.scrollTo('top');
    cy.findAllByLabelText('Name').type('[Test Data] ' + faker.lorem.sentence(), { force: true });
    cy.findAllByLabelText('Description').type(faker.lorem.sentence(), { force: true });
    return cy.get('form.taxonomy-term-form').find('input#edit-submit').click();
  },
};

Given('I create a {string} taxonomy term', (vocabulary) => {
  let creator = creators[vocabulary];
  assert.isNotNull(creator, `I do not know how to create ${vocabulary} taxonomy terms yet.  Please add a definition in ${__filename}.`);
  creator().then(() => {
    cy.getDrupalSettings().then((drupalSettings) => {
      const currentPath = drupalSettings.path.currentPath;
      cy.wrap(currentPath.split('/').pop()).as('termId');
      cy.window().then((window) => {
        const pagePath = window.location.pathname;
        cy.wrap(pagePath).as('pagePath');
      });
    });
  });
});
