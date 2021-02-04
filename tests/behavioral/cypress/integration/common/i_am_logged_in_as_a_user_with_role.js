import { Then } from "cypress-cucumber-preprocessor/steps";
import faker from "faker";

Then(`I am logged in as a user with role {string}`, (text) => {
  let username = faker.internet.userName();
  let password = faker.internet.password();
  cy.drupalAddUserWithRole(text, username, password);
  cy.drupalLogin(username, password);
});
