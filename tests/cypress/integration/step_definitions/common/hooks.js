import { After } from "@badeball/cypress-cucumber-preprocessor";

After(() => {
  // Clean up all entities created during this test
  cy.cleanupTestContent();
});
