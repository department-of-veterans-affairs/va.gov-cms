import { Then } from "@badeball/cypress-cucumber-preprocessor";

Then("I run the drush command {string}", (command) => {
  cy.drupalDrushCommand(command);
});
