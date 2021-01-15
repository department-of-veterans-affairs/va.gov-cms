describe('Component accessibility test', () => {
  // Test homepage while logged out.
  it('Login page has no detectable accessibility violations on load.', () => {
    cy.visit('/');
    cy.injectAxe();

    const axeRuntimeOptions = {
      runOnly: {
        type: 'tag',
        values: ['wcag2a', 'wcag2aa']
      }
    };

    cy.get('body').each((element, index) => {
      cy.checkA11y(null, null, cy.terminalLog);
    });
  });
});
