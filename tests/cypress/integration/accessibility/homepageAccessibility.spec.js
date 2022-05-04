const axeRuntimeOptions = {
  runOnly: {
    type: 'tag',
    values: ['wcag2a', 'wcag2aa']
  }
};

describe('Component accessibility test', () => {
  it('Login page has no detectable accessibility violations on load.', () => {
    cy.visit('/');
    cy.injectAxe();
    cy.checkA11y({
      include: 'body',
      exclude: ['#jsd-widget'],
    }, null, cy.terminalLog);
  });
});
