import { Then } from "cypress-cucumber-preprocessor/steps";

Then(`I trigger a content release`, () => {
  cy.drupalContentRelease();
});
