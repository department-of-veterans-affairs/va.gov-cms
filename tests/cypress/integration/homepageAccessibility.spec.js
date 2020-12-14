describe('Component accessibility test', () => {
  // Test homepage while logged out.
  it('Login page has no detectable accessibility violations on load.', () => {
    cy.visit('/');
    cy.injectAxe();
    
    cy.get('body').each((element, index) => {
      cy.checkA11y(null, null, cy.terminalLog);
    });
  });
});
