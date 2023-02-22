import { Then } from "cypress-cucumber-preprocessor/steps";

Then(`I log back in`, function handler() {
  cy.drupalLogin(this.username, this.password);
});
