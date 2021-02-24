import '@testing-library/cypress/add-commands';
import 'cypress-file-upload';

const compareSnapshotCommand = require('cypress-visual-regression/dist/command');

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
  if (Cypress.env('VAGOV_INTERACTIVE')) {
    cmd = 'lando drush %command';
  }
  if (typeof command === 'string') {
    command = [
      command,
    ];
  }
  return cy.exec(cmd.replace('%command', command.join(' ')));
});

Cypress.Commands.add('drupalDrushEval', (php) => {
  return cy.drupalDrushCommand([
    'eval',
    `'${php.replace(/'/g, `'\\''`)}'`,
  ]);
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

Cypress.Commands.add('iframe', { prevSubject: 'element' }, ($iframe, callback = () => { }) => {
  return cy
    .wrap($iframe)
    .should(iframe => expect(iframe.contents().find('body')).to.exist)
    .then(iframe => cy.wrap(iframe.contents().find('body')));
});

Cypress.Commands.add("type_ckeditor", (element, content) => {
  cy.window().then((win) => {
    win.CKEDITOR.instances[element].setData(content);
  });
});

Cypress.Commands.add('scrollToSelector', (selector) => {
  cy.document().then((document) => {
    const htmlElement = document.querySelector('html');
    if (htmlElement) {
      htmlElement.style.scrollBehavior = 'inherit';
    }
  })
  cy.get(selector).scrollIntoView({ offset: {top: 0}});
  return cy.get(selector);
})

compareSnapshotCommand();
