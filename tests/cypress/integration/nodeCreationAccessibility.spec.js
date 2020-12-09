const routes = ['/node/add/page', '/node/add/q_a'];

describe('Component accessibility test', () => {
  routes.forEach((route) => {

    const testName = `${route} has no detectable accessibility violations on load.`;
    it(testName, () => {
      // @TODO Use Cypress.env variables for user/pass.
      // @TODO Use a content admin role.
      cy.login('axcsd452ksey', 'drupal8');

      cy.visit(route);
      cy.injectAxe();
      
      cy.get('body').each((element, index) => {
        cy.checkA11y(null, null, terminalLog);
      });
    });
  });
});
