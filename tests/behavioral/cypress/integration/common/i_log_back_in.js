import { Then } from "cypress-cucumber-preprocessor/steps";
import faker from "faker";

Then(`I log back in`, () => {
  cy.all(cy.get('@username'), cy.get('@password'))
    .then((values) => {
      cy.drupalLogin(values[0], values[1]);
    });
});
