import { Then } from "cypress-cucumber-preprocessor/steps";
import { faker } from "@faker-js/faker";

Then(`I am logged in as a user with the roles {string}`, (text) => {
  const username = `test_${faker.internet.userName()}`;
  const password = faker.internet.password();
  cy.wrap(username).as("username");
  cy.wrap(password).as("password");
  const roles = text.split(", ");
  cy.drupalAddUserWithRoles(roles, username, password);
  cy.drupalLogin(username, password);
});
