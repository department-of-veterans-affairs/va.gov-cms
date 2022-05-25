import { Given } from "cypress-cucumber-preprocessor/steps";

Given(`I publish the node`, () => {
  cy.get('@nodeId').then((nid) => {
    cy.visit(`/node/${nid}/edit`);
    cy.scrollToSelector('select#edit-moderation-state-0-state');
    cy.get('select#edit-moderation-state-0-state').select('Published', { force: true });
    cy.get('form.node-form').find('input#edit-submit').click();
  });
});
