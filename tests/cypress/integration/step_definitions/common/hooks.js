import { Before, After } from "@badeball/cypress-cucumber-preprocessor";

Before(() => {
  // Optional: Log test start
  cy.log(`Starting test: ${(this.pickle && this.pickle.name) || "Unknown"}`);
});

After(() => {
  // Clean up all entities created during this test
  cy.cleanupTestContent();
});
