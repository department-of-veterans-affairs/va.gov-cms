exports.terminalLog = (violations) => {
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
      target: nodes[idx] ? nodes[idx].target : null,
      nodes: nodes.length
    })
  )

  cy.task('table', violationData);
}
