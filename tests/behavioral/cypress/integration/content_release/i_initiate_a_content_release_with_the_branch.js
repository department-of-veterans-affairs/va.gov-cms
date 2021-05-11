import { Given } from "cypress-cucumber-preprocessor/steps";

Given(`I initiate a content release with the branch {string}`, (branchName) => {
  cy.visit('/admin/content/deploy');
  cy.get('input#edit-selection-choose').click({ force: true });
  cy.scrollToSelector('#edit-selection-choose').wait(100);
  cy.get('input#edit-git-ref')
    .type(branchName, { force: true })
    .wait(3000)
    .type('{downarrow}', { force: true })
    .type('{enter}', { force: true })
    .blur();
  cy.get('input#edit-submit').click({ force: true });
});
