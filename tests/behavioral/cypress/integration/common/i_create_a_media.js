import { Given } from "cypress-cucumber-preprocessor/steps";
import faker from "faker";

const creators = {
  image: () => {
    cy.visit('/media/add/image');
    cy.scrollTo('top');
    cy.findAllByLabelText('Name').type('[Test Data] ' + faker.lorem.sentence(), { force: true });
    cy.findAllByLabelText('Description').type(faker.lorem.sentence(), { force: true });
    cy.findAllByLabelText('Section').select('Veterans Affairs');
    cy.get('#edit-image-0-upload').attachFile('images/polygon_image.png').wait(1000);
    cy.findAllByLabelText('Alternative text').type(faker.lorem.sentence(), { force: true });
    const resizePoint = cy.get('span.cropper-face.cropper-move');
    resizePoint.scrollIntoView();
    cy.scrollToSelector('.image-widget-data');
    cy.window().then((window) => {
      const POINTER_DOWN = window.PointerEvent ? 'pointerdown' : 'mousedown';
      const POINTER_MOVE = window.PointerEvent ? 'pointermove' : 'mousemove';
      const POINTER_UP = window.PointerEvent ? 'pointerup' : 'mouseup';
      const jQuery = window.jQuery;
      const cropperType = jQuery("[data-drupal-iwc=wrapper]").data('ImageWidgetCrop').types[0];
      const cropper = cropperType.cropper;
      const { dragBox } = cropper;
      const $wrapper = jQuery(dragBox).closest('.crop-preview-wrapper');
      const $cropBox = $wrapper.find('.cropper-crop-box');
      const $points = $wrapper.find('.cropper-point');
      const moveBox = jQuery('.cropper-face.cropper-move')[0].getBoundingClientRect();
      cy.wrap(dragBox)
        .trigger('mouseover', { force: true })
        .wait(100)
        .trigger(POINTER_DOWN, { which: 1, force: true })
        .wait(100)
        .trigger(POINTER_MOVE, 15, 15, { which: 1, force: true })
        .wait(100)
        .trigger(POINTER_MOVE, 50, 50, { which: 1, force: true })
        .wait(100)
        .trigger(POINTER_UP, { which: 1, force: true });
      const mediaImageUrl = jQuery('.image-widget-data').find('.file--image').find('a').attr('href');
      cy.wrap(mediaImageUrl).as('mediaImageUrl');
      cy.get('form.media-form').find('input#edit-submit').click();
      cy.window().then((window) => {
        const mediaPath = window.jQuery('[role="contentinfo"]').find('a').attr('href');
        const mediaId = mediaPath.split('/').pop();
        cy.wrap(mediaPath).as('mediaPath');
        cy.wrap(mediaId).as('mediaId');
        cy.visit(mediaPath);
      });
    });
  }
};

Given('I create a {string} media', (contentType) => {
  let creator = creators[contentType];
  assert.isNotNull(creator, `I do not know how to create ${contentType} media yet.  Please add a definition in ${__filename}.`);
  creator();
});

