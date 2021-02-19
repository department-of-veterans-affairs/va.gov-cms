import { Given } from "cypress-cucumber-preprocessor/steps";
import faker from "faker";

const creators = {
  image: () => {
    const POINTER_DOWN = window.PointerEvent ? 'pointerdown' : 'mousedown';
    const POINTER_MOVE = window.PointerEvent ? 'pointermove' : 'mousemove';
    const POINTER_UP = window.PointerEvent ? 'pointerup' : 'mouseup';
    cy.visit('/media/add/image');
    cy.scrollTo('top');
    cy.findAllByLabelText('Name').type('[Test Data] ' + faker.lorem.sentence(), { force: true });
    cy.findAllByLabelText('Description').type(faker.lorem.sentence(), { force: true });
    cy.findAllByLabelText('Owner').select('Veterans Affairs');
    cy.get('#edit-image-0-upload').attachFile('images/polygon_image.png').wait(1000);
    cy.findAllByLabelText('Alternative text').type(faker.lorem.sentence(), { force: true });
    const resizePoint = cy.get('span.cropper-face.cropper-move');
    resizePoint.scrollIntoView();
    cy.scrollToSelector('.image-widget-data');
    cy.window().then((window) => {
      const jQuery = window.jQuery;
      const cropper = jQuery("[data-drupal-iwc=wrapper]").data('ImageWidgetCrop').types[0].cropper;
      const { dragBox } = cropper;
      const $wrapper = jQuery(dragBox).closest('.crop-preview-wrapper');
      const $cropBox = $wrapper.find('.cropper-crop-box');
      const $points = $wrapper.find('.cropper-point');
      jQuery(dragBox)
        .on('mouseover', (event) => {
          console.log('mouseover');
        })
        .on('pointerdown', (event) => {
          console.log('pointerdown');
        })
        .on('pointermove', (event) => {
          console.log('pointermove');
        })
        .on('pointerup', (event) => {
          console.log('pointerup');
        });
      $cropBox
        .on('cropStart', (event) => {
          console.log('cropStart');
        })
        .on('cropMove', (event) => {
          console.log('cropMove');
        })
        .on('cropEnd', (event) => {
          console.log('cropEnd');
        });
      cy.wrap($cropBox)
        .trigger('mouseover', { force: true })
        .trigger('pointerdown', { which: 1, force: true })
        .trigger('pointermove', { clientX: 5, clientY: 5, force: true })
        .trigger('pointermove', { clientX: 35, clientY: 35, force: true })
        .trigger('pointerup', { force: true });
      cy.wrap($wrapper)
        .trigger('mouseover', { force: true })
        .trigger('pointerdown', { which: 1, force: true })
        .trigger('pointermove', { clientX: 5, clientY: 5, force: true })
        .trigger('pointermove', { clientX: 35, clientY: 35, force: true })
        .trigger('pointerup', { force: true });
      cy.wait(1000);
    });
  }
};

Given('I create a {string} media', (contentType) => {
  let creator = creators[contentType];
  assert.isNotNull(creator, `I do not know how to create ${contentType} media yet.  Please add a definition in ${__filename}.`);
  creator();
});

