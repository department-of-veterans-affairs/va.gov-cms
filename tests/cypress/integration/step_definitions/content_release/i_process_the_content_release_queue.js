import { Given } from "@badeball/cypress-cucumber-preprocessor";

Given(`I process the content release queue`, () => {
  cy.drupalDrushCommand("advancedqueue:queue:process content_release");
});
