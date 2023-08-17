import { Then } from "cypress-cucumber-preprocessor/steps";

Then(`I log out`, () => {
  cy.drupalLogout();
});
