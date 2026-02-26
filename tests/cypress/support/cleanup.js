// Global entity tracking
let createdEntities = {
  nodes: [],
  media: [],
  paragraphs: [],
  taxonomyTerms: [],
  files: [],
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

  // Delete in reverse order of creation to handle dependencies
  // Order: paragraphs -> media -> nodes -> taxonomy terms

  if (createdEntities.paragraphs.length > 0) {
    createdEntities.paragraphs.reverse().forEach(({ id }) => {
      cy.drupalDrushCommand(`entity:delete paragraph ${id} --yes`, {
        failOnNonZeroExit: false,
      });
    });
  }

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

  // Reset tracking
  createdEntities = {
    nodes: [],
    media: [],
    paragraphs: [],
    taxonomyTerms: [],
    files: [],
  };

  cy.log("Test cleanup complete");
});

// Get tracking data (useful for debugging)
Cypress.Commands.add("getTrackedEntities", () => {
  return cy.wrap(createdEntities);
});
