// Global entity tracking
let createdEntities = {
  nodes: [],
  media: [],
  taxonomyTerms: [],
  users: [],
};

// Track any entity type
Cypress.Commands.add("trackEntity", (type, id, metadata = {}) => {
  if (!createdEntities[type]) {
    createdEntities[type] = [];
  }
  createdEntities[type].push({ id, ...metadata });
  cy.log(`Tracked ${type} entity: ${id}`);
});

// Main cleanup command
Cypress.Commands.add("cleanupTestContent", () => {
  cy.log("Starting test cleanup...");

  if (createdEntities.media.length > 0) {
    createdEntities.media.reverse().forEach(({ id }) => {
      cy.drupalDrushCommand(`entity:delete media ${id} --yes`, {
        failOnNonZeroExit: false,
      });
    });
  }

  if (createdEntities.nodes.length > 0) {
    createdEntities.nodes.reverse().forEach(({ id }) => {
      cy.drupalDrushCommand(`entity:delete node ${id} --yes`, {
        failOnNonZeroExit: false,
      });
    });
  }

  if (createdEntities.taxonomyTerms.length > 0) {
    createdEntities.taxonomyTerms.reverse().forEach(({ id }) => {
      cy.drupalDrushCommand(`entity:delete taxonomy_term ${id} --yes`, {
        failOnNonZeroExit: false,
      });
    });
  }

  if (createdEntities.users.length > 0) {
    createdEntities.users.reverse().forEach(({ id }) => {
      cy.drupalDrushCommand(`entity:delete user ${id} --yes`, {
        failOnNonZeroExit: false,
      });
    });
  }

  // Reset tracking
  createdEntities = {
    nodes: [],
    media: [],
    paragraphs: [],
    taxonomyTerms: [],
    files: [],
    users: [],
  };

  cy.log("Test cleanup complete");
});

// Get tracking data (useful for debugging)
Cypress.Commands.add("getTrackedEntities", () => {
  return cy.wrap(createdEntities);
});
