import { Then } from "@badeball/cypress-cucumber-preprocessor";

Then(`I log out`, () => {
  cy.drupalLogout();
});
