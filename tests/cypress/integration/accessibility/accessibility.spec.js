/* eslint-disable no-console */
/* eslint-disable max-nested-callbacks */
const routes = [
  // Home page
  "/",
  "/node/add/page",
  "/node/add/landing_page",
  "/node/add/documentation_page",
  "/node/add/event",
  "/node/add/health_care_local_facility",
  "/node/add/health_care_region_detail_page",
  "/node/add/health_care_region_page",
  "/node/add/office",
  "/node/add/outreach_asset",
  "/node/add/person_profile",
  "/node/add/press_release",
  "/node/add/q_a",
  "/node/add/regional_health_care_service_des",
  "/node/add/news_story",
  "/node/add/support_service",
  "/user",
];

before(() => {
  // @TODO Use Cypress.env variables for user/pass.
  // @TODO Use a content admin role.
  // Ensure there is no active user session.
  cy.drupalLogout();
  cy.drupalLogin("axcsd452ksey", "drupal8");

  // Preserve the Drupal session cookie to avoid having to login
  // before testing each page.
  const cookies = cy.getCookies();
  cookies.each((cookie) => {
    if (cookie.name.match(/SS?ESS/)) {
      Cypress.Cookies.defaults({
        preserve: cookie.name,
      });
    }
  });
});

const axeContext = {
  include: [["body"]],
  exclude: [
    [
      "#edit-menu-menu-parent", // 8700-item select elements apparently break accessibility tests
    ],
  ],
};

const axeRuntimeOptions = {
  runOnly: {
    type: "tag",
    values: ["wcag2a", "wcag2aa"],
  },
};

const allViolations = [];

describe("Component accessibility test", () => {
  routes.forEach((route) => {
    const testName = `${route} has no detectable accessibility violations on load.`;
    it(testName, () => {
      cy.visit(route);
      cy.injectAxe();
      cy.checkA11y(axeContext, axeRuntimeOptions, (violations) => {
        cy.accessibilityLog(violations);
        const violationData = violations.map((violation) => ({
          route,
          ...violation,
        }));
        allViolations.push(...violationData);
      });
    });
  });
});

after(() => {
  cy.writeFile(
    "cypress_accessibility_errors.json",
    JSON.stringify(allViolations)
  );
});
