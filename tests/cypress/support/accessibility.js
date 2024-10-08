/* eslint-disable no-console */
const axeContext = {
  include: [["body"]],
  exclude: [
    [
      "#edit-menu-menu-parent", // 8700-item select elements apparently break accessibility tests.
    ],
    [
      "iframe#jsd-widget", // Not really under our control.
    ],
    [
      "ul.pager__items", // See department-of-veterans-affairs/va.gov-cms#12912.
    ],
  ],
};

const axeRuntimeOptions = {
  runOnly: {
    type: "tag",
    values: ["wcag2a", "wcag2aa", "wcag21a", "wcag21aa"],
  },
  rules: {
    "aria-allowed-attr": { enabled: false }, // See department-of-veterans-affairs/va.gov-cms#14358
  },
};

Cypress.Commands.add("checkAccessibility", () => {
  cy.wait(1000);
  return cy.checkA11y(axeContext, axeRuntimeOptions, (violations) => {
    cy.accessibilityLog(violations);
    return cy.location("pathname").then((route) => {
      // eslint-disable-next-line max-nested-callbacks
      const violationData = violations.map((violation) => ({
        route,
        ...violation,
      }));
      const accessibilityViolations = Cypress.config("accessibilityViolations");
      accessibilityViolations.push(...violationData);
      Cypress.config("accessibilityViolations", accessibilityViolations);
    });
  });
});

Cypress.Commands.add("accessibilityLog", (violations) => {
  const violationData = violations.map(
    ({ id, impact, description, nodes }) => ({
      id,
      impact,
      description,
      target: nodes[0].target,
      nodes: nodes.length,
    })
  );
  cy.task("table", violationData);
});

before(() => {
  const accessibilityViolations = [];
  Cypress.config("accessibilityViolations", accessibilityViolations);
});

after(() => {
  const accessibilityViolations = Cypress.config("accessibilityViolations");
  const fileName = "cypress_accessibility_violations.json";
  const violations = JSON.parse(
    JSON.stringify(accessibilityViolations, null, 2)
  );

  cy.task("readFileMaybe", fileName).then((result) => {
    if (result != null) {
      cy.readFile(fileName).then((list) => {
        for (let step = 0; step < violations.length; step++) {
          list.push(violations[step]);
        }
        cy.writeFile(fileName, list);
      });
    } else {
      cy.writeFile(fileName, violations);
    }
  });
});
