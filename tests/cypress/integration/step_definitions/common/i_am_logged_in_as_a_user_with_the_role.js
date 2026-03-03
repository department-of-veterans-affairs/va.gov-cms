import { Then } from "@badeball/cypress-cucumber-preprocessor";
import { faker } from "@faker-js/faker";

Then(`I am logged in as a user with the {string} role`, (text) => {
  const username = `test_${faker.internet.userName()}`;
  const password = faker.internet.password();
  cy.wrap(username).as("username");
  cy.wrap(password).as("password");
  cy.drupalAddUserWithRole(text, username, password);
  cy.drupalLogin(username, password);
  cy.drupalGetUserIdByUsername(username).then((userId) => {
    cy.log(`Tracking user with user ID: ${userId}`);
    cy.trackEntity("users", userId, {});
  });
});
