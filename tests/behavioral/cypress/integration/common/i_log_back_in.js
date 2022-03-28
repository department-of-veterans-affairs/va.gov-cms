import { Then } from "cypress-cucumber-preprocessor/steps";
import faker from "faker";

Then(`I log back in`, function () {
  cy.drupalLogin(this.username, this.password);
});
