import { Then } from "@badeball/cypress-cucumber-preprocessor";

Then(`I trigger a content release`, () => {
  cy.drupalContentRelease();
});
