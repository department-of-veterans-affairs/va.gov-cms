Cypress.Commands.add('login', (user, password) => {
  return cy.request({
    method: 'POST',
    url: '/user/login',
    form: true,
    body: {
      name: user,
      pass: password,
      form_id: 'user_login_form'
    }
  });
});

Cypress.Commands.add('logout', () => {
  return cy.request('/user/logout');
});

Cypress.Commands.add('terminalLog', (violations) => {
  const violationData = violations.map(
    ({ id, impact, description, nodes }) => ({
      id,
      impact,
      description,
      target: nodes[0].target,
      nodes: nodes.length
    })
  )

  cy.task('table', violationData);
});
