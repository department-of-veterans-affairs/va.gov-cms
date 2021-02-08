import { Then } from "cypress-cucumber-preprocessor/steps";
import faker from "faker";

Then(`I am logged in as a user with the {string} role`, (text) => {
  let username = 'test_' + faker.internet.userName();
  let password = faker.internet.password();
  cy.drupalAddUserWithRole(text, username, password);
  cy.drupalLogin(username, password);
});
