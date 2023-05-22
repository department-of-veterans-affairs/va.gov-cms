import { Then } from "cypress-cucumber-preprocessor/steps";
import { faker } from "@faker-js/faker";

Then(`I am logged in as a user with the {string} role`, (text) => {
  const username = `test_${faker.internet.userName()}`;
  const password = faker.internet.password();
  cy.wrap(username).as("username");
  cy.wrap(password).as("password");
  cy.drupalAddUserWithRole(text, username, password);
  cy.drupalLogin(username, password);
});
