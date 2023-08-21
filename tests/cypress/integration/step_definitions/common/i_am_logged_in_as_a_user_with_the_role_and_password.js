import { Then } from "@badeball/cypress-cucumber-preprocessor";
import { faker } from "@faker-js/faker";

Then(
  `I am logged in as a user with the {string} role and password {string}`,
  (role, password) => {
    const username = `test_${faker.internet.userName()}`;
    cy.wrap(username).as("username");
    cy.drupalAddUserWithRole(role, username, password);
    cy.drupalLogin(username, password);
  }
);
