// @TODO move this to a separate file.
function terminalLog(violations) {
  cy.task(
    'log',
    `${violations.length} accessibility violation${
      violations.length === 1 ? '' : 's'
    } ${violations.length === 1 ? 'was' : 'were'} detected`
  );
  // pluck specific keys to keep the table readable
  const violationData = violations.map(
    ({ id, impact, description, nodes }, idx) => ({
      id,
      impact,
      description,
      target: nodes[idx].target,
      nodes: nodes.length
    })
  )

  cy.task('table', violationData);
}

describe('Component accessibility test', () => {
  // Test homepage while logged out.
  it('Login page has no detectable accessibility violations on load.', () => {
    cy.visit('/');
    cy.injectAxe();
    
    cy.get('body').each((element, index) => {
      cy.checkA11y(null, null, terminalLog);
    });
  });
});
