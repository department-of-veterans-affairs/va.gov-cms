const routes = [
  '/sections',
  '/node/add/page',
  '/node/add/landing_page',
  '/node/add/documentation_page',
  '/node/add/event',
  '/node/add/health_care_local_facility',
  '/node/add/health_care_region_detail_page',
  '/node/add/health_care_region_page',
  '/node/add/office',
  '/node/add/outreach_asset',
  '/node/add/person_profile',
  '/node/add/press_release',
  '/node/add/q_a',
  '/node/add/regional_health_care_service_des',
  '/node/add/news_story',
  '/node/add/support_service',
  '/user',
];

describe('Component accessibility test', () => {
  routes.forEach((route) => {

    const testName = `${route} has no detectable accessibility violations on load.`;
    it(testName, () => {
      // @TODO Use Cypress.env variables for user/pass.
      // @TODO Use a content admin role.
      cy.login('axcsd452ksey', 'drupal8');

      cy.visit(route);
      cy.injectAxe();

      const axeRuntimeOptions = {
        runOnly: {
          type: 'tag',
          values: ['wcag2a', 'wcag2aa']
        }
      };

      cy.get('body').each((element, index) => {
        cy.checkA11y(null, axeRuntimeOptions, cy.terminalLog);
      });
    });
  });
});
