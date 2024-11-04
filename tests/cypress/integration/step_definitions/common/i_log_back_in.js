import { Then } from "@badeball/cypress-cucumber-preprocessor";

Then(`I log back in`, function handler() {
  cy.drupalLoginViaUi(this.username, this.password);
});
