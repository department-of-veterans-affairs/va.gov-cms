import { Then } from "@badeball/cypress-cucumber-preprocessor";

Then(`I log back in`, function handler() {
  cy.drupalLogin(this.username, this.password);
});
