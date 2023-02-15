import { Given } from "cypress-cucumber-preprocessor/steps";

Given(`I process the content release queue`, () => {
  cy.drupalDrushCommand("advancedqueue:queue:process content_release");
});
