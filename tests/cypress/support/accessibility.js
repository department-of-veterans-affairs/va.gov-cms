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

const accessibilityViolations = [];

Cypress.Commands.add("checkAccessibility", () => {
  return cy.checkA11y(axeContext, axeRuntimeOptions, (violations) => {
    cy.accessibilityLog(violations);
    cy.location("pathname").then((route) => {
      // eslint-disable-next-line max-nested-callbacks
      const violationData = violations.map((violation) => ({
        route,
        ...violation,
      }));
      accessibilityViolations.push(...violationData);
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

after(() => {
  cy.writeFile(
    "cypress_accessibility_violations.json",
    accessibilityViolations
  );
});
