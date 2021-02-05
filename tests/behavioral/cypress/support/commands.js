import '@testing-library/cypress/add-commands';

// ***********************************************
// This example commands.js shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************
//
//
// -- This is a parent command --
// Cypress.Commands.add("login", (email, password) => { ... })
//
//
// -- This is a child command --
// Cypress.Commands.add("drag", { prevSubject: 'element'}, (subject, options) => { ... })
//
//
// -- This is a dual command --
// Cypress.Commands.add("dismiss", { prevSubject: 'optional'}, (subject, options) => { ... })
//
//
// -- This will overwrite an existing command --
// Cypress.Commands.overwrite("visit", (originalFn, url, options) => { ... })

Cypress.Commands.add('drupalLogin', (username, password) => {
  cy.visit('/user/login');
  cy.get('#edit-name').type(username);
  cy.get('#edit-pass').type(password);
  cy.get('#edit-submit').click();
});

Cypress.Commands.add('drupalLogout', () => {
  cy.visit('/user/logout');
});

Cypress.Commands.add('drupalDrushCommand', (command) => {
  let cmd = 'drush %command';
  if (typeof command === 'string') {
    command = [
      command,
    ];
  }
  return cy.exec(cmd.replace('%command', command.join(' ')));
});

Cypress.Commands.add('drupalDrushUserCreate', (username, password) => {
  cy.drupalDrushCommand([
    'user:create',
    username,
    `--password=${password}`,
    `--mail=${username}@example.org`,
  ]);
});

Cypress.Commands.add('drupalDrushUserRoleAdd', (username, role) => {
  cy.drupalDrushCommand([
    'urol',
    role,
    username,
  ]);
});

Cypress.Commands.add('drupalAddUserWithRole', (role, username, password) => {
  cy.drupalDrushUserCreate(username, password);
  cy.drupalDrushUserRoleAdd(username, role);
});

Cypress.Commands.add('iframe', { prevSubject: 'element' }, ($iframe) => {
  return new Cypress.Promise(resolve => {
    resolve($iframe.contents().find('body'));
  });
});
