import { Given } from "cypress-cucumber-preprocessor/steps";
import faker from "faker";

const creators = {
  documentation_page: () => {
    cy.visit('/node/add/documentation_page');
    cy.scrollTo('top');
    cy.findAllByLabelText('Page title').type('[Test Data] ' + faker.lorem.sentence(), { force: true });
    cy.findAllByLabelText('Page introduction').type(faker.lorem.sentence(), { force: true });
    cy.get('form.node-form').find('input#edit-submit').click();
  }
};

Given(`I create a {string} node`, (contentType) => {
  let creator = creators[contentType];
  assert.isNotNull(creator, `I do not know how to create ${contentType} nodes yet.  Please add a definition in ${__filename}.`);
  creators[contentType]();
});
