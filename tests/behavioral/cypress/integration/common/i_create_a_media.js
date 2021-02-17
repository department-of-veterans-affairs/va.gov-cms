import { Given } from "cypress-cucumber-preprocessor/steps";
import faker from "faker";

const moveCropCorner = (selector, x, y) => {
  cy.get('[data-drupal-iwc=type]')
    .first()
    .scrollTo('center');
  cy.get(selector)
    .trigger('mousedown', { which: 1, force: true })
    .trigger('mousemove', { clientX: x, clientY: y, force: true })
    .trigger('mouseup', { force: true });
}


const creators = {
  image: () => {
    cy.visit('/media/add/image');
    cy.scrollTo('top');
    cy.findAllByLabelText('Name').type('[Test Data] ' + faker.lorem.sentence(), { force: true });
    cy.findAllByLabelText('Description').type(faker.lorem.sentence(), { force: true });
    cy.findAllByLabelText('Owner').select('Veterans Affairs');
    cy.get('#edit-image-0-upload').attachFile('images/polygon_image.png');
    cy.findAllByLabelText('Alternative text').type(faker.lorem.sentence(), { force: true });
    moveCropCorner('.point-nw', 120, 120);
    cy.get('form.media-form').find('input#edit-submit').click();
  }
};

Given('I create a {string} media', (contentType) => {
  let creator = creators[contentType];
  assert.isNotNull(creator, `I do not know how to create ${contentType} media yet.  Please add a definition in ${__filename}.`);
  creator();
});
