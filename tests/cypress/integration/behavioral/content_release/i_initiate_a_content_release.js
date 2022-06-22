import { Given } from "cypress-cucumber-preprocessor/steps";

Given(`I initiate a content release`, () => {
  cy.get('input[type="submit"][value="Release content"]').click({ force: true });
});
