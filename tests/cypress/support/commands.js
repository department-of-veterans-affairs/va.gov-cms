Cypress.Commands.add('login', (user, password) => {
  console.log(`in login for user ${user}`);
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
