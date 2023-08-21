import { Given } from "@badeball/cypress-cucumber-preprocessor";

Given(`I initiate a content release`, () => {
  cy.get('input[type="submit"][value="Release content"]').click({
    force: true,
  });
});
