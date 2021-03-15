import { Then } from "cypress-cucumber-preprocessor/steps";
import faker from "faker";

Then(`I am logged in as a user with the {string} role and password {string}`, (role, password) => {
  let username = 'test_' + faker.internet.userName();
  cy.wrap(username).as('username');
  cy.drupalAddUserWithRole(role, username, password);
  cy.drupalLogin(username, password);
});
