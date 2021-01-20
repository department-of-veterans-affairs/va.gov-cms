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
      // Excluding JSD widget from A11y scan, since we cannot control its code.
      cy.checkA11y({ exclude: ['#jsd-widget']}, null, cy.terminalLog);
    });
  });
});
